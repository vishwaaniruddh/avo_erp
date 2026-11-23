<?php

namespace App\Services;

use App\Core\Database;
use App\Models\AuditLog;
use InvalidArgumentException;
use RuntimeException;
use PDO;

class ScrapService
{
    /**
     * Submit Physical Scrap Discharge Entry (Write-Only Gate - RA-07)
     */
    public static function submitDischarge(array $data, array $user): array
    {
        if (empty($data['material_type']) || empty($data['weight_kg']) || empty($data['defect_reason'])) {
            throw new InvalidArgumentException("Material Type, Weight (kg), and Defect Reason are mandatory");
        }

        $weightKg = (float) $data['weight_kg'];
        if ($weightKg <= 0) {
            throw new InvalidArgumentException("Weight must be greater than zero kg");
        }

        // Automatic estimated valuation calculation based on standard salvage rates
        $rates = [
            'Tested Battery Cells (Lithium)' => 28.50,
            'Copper Wire Coils' => 12.00,
            'Salvageable Inverter PCBs' => 45.00,
            'Transformer Core Scrap' => 18.00,
            'Aluminium Heat Sinks' => 8.50,
        ];
        $rate = $rates[$data['material_type']] ?? 15.00;
        $estValue = round($weightKg * $rate, 2);

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(<<<SQL
INSERT INTO scrap_ledger (
    material_type, weight_kg, serial_number, defect_reason,
    warehouse_branch, operator_id, operator_name, est_recovery_value
) VALUES (
    :mat, :wt, :sn, :df,
    :wb, :oid, :oname, :val
)
SQL
        );

        $stmt->execute([
            'mat' => trim($data['material_type']),
            'wt' => $weightKg,
            'sn' => $data['serial_number'] ?? ('SN-SCRAP-' . strtoupper(substr(uniqid(), -6))),
            'df' => trim($data['defect_reason']),
            'wb' => $data['warehouse_branch'] ?? ($user['branch'] ?? 'Central Storage Hub'),
            'oid' => $user['id'],
            'oname' => $user['name'],
            'val' => $estValue,
        ]);

        $id = (int) $pdo->lastInsertId();

        AuditLog::log(
            $user['id'],
            $user['name'],
            $user['role'],
            'SCRAP_DISCHARGE_LOGGED',
            'ScrapLedger',
            (string) $id,
            null,
            [
                'material_type' => $data['material_type'],
                'weight_kg' => $weightKg,
                'branch' => $data['warehouse_branch'] ?? 'Central Hub',
            ]
        );

        return [
            'id' => $id,
            'status' => 'discharged',
            'message' => 'Scrap discharge record securely logged into audit stream. Blind entry active (running total masked).',
            'timestamp' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * Read Scrap Ledger (Enforces Anti-Collusion Gate - RA-07, RA-08)
     */
    public static function getLedger(array $user): array
    {
        $roleType = $user['role_type'] ?? 'Operations/Warehouse';
        $level = $user['level'] ?? 'L1';

        // STRICT BLIND ENTRY CHECK (RA-07)
        // Warehouse operators (Level L1) are blind to past ledger totals!
        if ($roleType === 'Operations/Warehouse' && $level === 'L1') {
            throw new RuntimeException("Blind Entry Security Gate (RA-07): Warehouse operators are prohibited from reading cumulative scrap ledgers to prevent inventory collusion.");
        }

        // RA-08: Executive, CFO, COO, L3 or Admin
        $isExecutive = in_array($roleType, ['Executive', 'Admin']) || ($roleType === 'Finance' && in_array($level, ['L2', 'L3'])) || $level === 'L3';
        if (!$isExecutive) {
            throw new RuntimeException("Permission denied: Executive or CFO sign-off required to audit scrap recovery ledger (RA-08).");
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT * FROM scrap_ledger ORDER BY id DESC");
        $records = $stmt->fetchAll();

        // Compute cumulative summaries
        $totalWeight = 0;
        $totalRecoveryValue = 0;
        $byMaterial = [];

        foreach ($records as $r) {
            $wt = (float) $r['weight_kg'];
            $val = (float) $r['est_recovery_value'];
            $totalWeight += $wt;
            $totalRecoveryValue += $val;

            $mat = $r['material_type'];
            if (!isset($byMaterial[$mat])) {
                $byMaterial[$mat] = ['material' => $mat, 'weight' => 0, 'value' => 0];
            }
            $byMaterial[$mat]['weight'] += $wt;
            $byMaterial[$mat]['value'] += $val;
        }

        return [
            'records' => $records,
            'summary' => [
                'total_entries' => count($records),
                'total_weight_kg' => round($totalWeight, 2),
                'total_recovery_value' => round($totalRecoveryValue, 2),
                'material_breakdown' => array_values($byMaterial),
            ],
            'gate_status' => 'Executive Access Verified (RA-08)',
        ];
    }
}

