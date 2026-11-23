<?php

namespace App\Services;

use App\Core\Database;
use App\Models\AuditLog;
use InvalidArgumentException;
use PDO;

class BiddingService
{
    /**
     * Fetch RFP Bids with Chinese-Wall Isolation (RA-04, RA-05)
     */
    public static function getBids(array $user): array
    {
        $pdo = Database::getConnection();
        $roleType = $user['role_type'] ?? 'Sales';
        $level = $user['level'] ?? 'L1';
        $userId = (int) $user['id'];

        $isExecutive = in_array($roleType, ['Executive', 'Admin']) || ($roleType === 'Sales' && $level === 'L3');

        if ($isExecutive) {
            // Executive / Owner View (RA-04, RA-05):
            // Sees ALL quotes side-by-side, grouped with silent RFP deduplication
            $stmt = $pdo->query("SELECT * FROM rfp_bids ORDER BY silent_link_id DESC, id DESC");
            $bids = $stmt->fetchAll();

            // Calculate unified company hit rate (duplicate RFPs count ONCE)
            $uniqueRFPs = [];
            $totalBids = count($bids);
            foreach ($bids as $b) {
                $uniqueRFPs[$b['normalized_rfp']] = true;
            }
            $uniqueTenderCount = count($uniqueRFPs);

            return [
                'bids' => $bids,
                'view_mode' => 'executive_unified',
                'chinese_wall_active' => true,
                'total_bids' => $totalBids,
                'unique_rfp_count' => $uniqueTenderCount,
                'deduplication_rate' => $totalBids > 0 ? round((1 - ($uniqueTenderCount / $totalBids)) * 100, 1) : 0,
            ];
        }

        // STRICT CHINESE-WALL ISOLATION (RA-04):
        // Salesperson sees ONLY their own quotes. Completely blind to other internal salespeople
        // quoting competing Integrators for the same RFP!
        $stmt = $pdo->prepare("SELECT id, rfp_number, rfp_title, end_client, integrator_name, salesperson_id, salesperson_name, quote_amount, cost_amount, est_margin_pct, status, created_at FROM rfp_bids WHERE salesperson_id = :sid ORDER BY id DESC");
        $stmt->execute(['sid' => $userId]);
        $bids = $stmt->fetchAll();

        return [
            'bids' => $bids,
            'view_mode' => 'salesperson_isolated',
            'chinese_wall_active' => true,
            'salesperson_id' => $userId,
            'notice' => 'Chinese-Wall Bidding: Lateral visibility into competing quotes is strictly sealed.',
        ];
    }

    /**
     * Submit an RFP Quote with Background Silent Deduplication (RA-05)
     */
    public static function submitBid(array $data, array $user): array
    {
        if (empty($data['rfp_number']) || empty($data['integrator_name']) || empty($data['quote_amount'])) {
            throw new InvalidArgumentException("RFP Reference #, Integrator Name, and Quote Amount are required");
        }

        $rfpNumber = trim($data['rfp_number']);
        // Normalize RFP reference (e.g., '2026-NTPC-09' -> '2026ntpc09')
        $normalizedRfp = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $rfpNumber));

        $pdo = Database::getConnection();

        // Check if another salesperson has previously entered a quote for the same RFP
        $stmt = $pdo->prepare("SELECT id, silent_link_id FROM rfp_bids WHERE normalized_rfp = :nrfp LIMIT 1");
        $stmt->execute(['nrfp' => $normalizedRfp]);
        $existing = $stmt->fetch();

        // Silent Hash Link Generation (RA-05)
        // Linking occurs quietly in background with ZERO alert or hint to the salesperson
        if ($existing) {
            $silentLinkId = $existing['silent_link_id'] ?: ('LINK-' . strtoupper(substr(md5($normalizedRfp), 0, 8)));

            // Backfill existing bid if needed
            if (!$existing['silent_link_id']) {
                $up = $pdo->prepare("UPDATE rfp_bids SET silent_link_id = :sl WHERE id = :id");
                $up->execute(['sl' => $silentLinkId, 'id' => $existing['id']]);
            }
        } else {
            $silentLinkId = 'LINK-' . strtoupper(substr(md5($normalizedRfp), 0, 8));
        }

        $quoteAmount = (float) $data['quote_amount'];
        $costAmount = (float) ($data['cost_amount'] ?? ($quoteAmount * 0.72));
        $marginPct = $quoteAmount > 0 ? round((($quoteAmount - $costAmount) / $quoteAmount) * 100, 2) : 0.0;

        $insertSql = <<<SQL
INSERT INTO rfp_bids (
    rfp_number, normalized_rfp, rfp_title, end_client, integrator_name,
    salesperson_id, salesperson_name, quote_amount, cost_amount, est_margin_pct,
    status, silent_link_id
) VALUES (
    :num, :norm, :title, :client, :integ,
    :sid, :sname, :quote, :cost, :margin,
    'submitted', :silent_link
)
SQL;

        $insertStmt = $pdo->prepare($insertSql);
        $insertStmt->execute([
            'num' => $rfpNumber,
            'norm' => $normalizedRfp,
            'title' => $data['rfp_title'] ?? "Tender {$rfpNumber}",
            'client' => $data['end_client'] ?? 'Public Sector Undertaking',
            'integ' => trim($data['integrator_name']),
            'sid' => $user['id'],
            'sname' => $user['name'],
            'quote' => $quoteAmount,
            'cost' => $costAmount,
            'margin' => $marginPct,
            'silent_link' => $silentLinkId,
        ]);

        $bidId = (int) $pdo->lastInsertId();

        AuditLog::log(
            $user['id'],
            $user['name'],
            $user['role'],
            'SUBMIT_RFP_QUOTE',
            'RFPBid',
            (string) $bidId,
            null,
            [
                'rfp_number' => $rfpNumber,
                'integrator' => $data['integrator_name'],
                'amount' => $quoteAmount,
                'silent_dedup' => !empty($existing),
            ]
        );

        $fetchStmt = $pdo->prepare("SELECT * FROM rfp_bids WHERE id = :id");
        $fetchStmt->execute(['id' => $bidId]);
        return $fetchStmt->fetch();
    }
}

