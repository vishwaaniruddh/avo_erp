<?php

namespace App\Services;

use App\Core\Database;
use PDO;
use Exception;

class SalesService
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    // =========================================================================
    // 1. LEADS & PROSPECT LIFECYCLE
    // =========================================================================

    public function getLeads(array $filters, array $user): array
    {
        $sql = "SELECT l.*, u.name AS salesperson_name, u.email AS salesperson_email,
                       COUNT(DISTINCT q.id) AS quotes_count,
                       GROUP_CONCAT(DISTINCT q.quote_number ORDER BY q.id DESC SEPARATOR ', ') AS quote_numbers
                FROM sales_leads l
                LEFT JOIN users u ON l.assigned_to_user_id = u.id
                LEFT JOIN sales_quotations q ON q.lead_id = l.id
                WHERE 1=1";
        $params = [];

        // Scope check: 'own' scope restricts L1 sales reps to only their assigned leads
        if (($user['scope'] ?? 'own') === 'own' && ($user['level'] ?? 'L1') === 'L1' && ($user['role_type'] ?? '') === 'Sales') {
            $sql .= " AND l.assigned_to_user_id = ?";
            $params[] = $user['id'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND l.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['sale_type'])) {
            $sql .= " AND l.sale_type = ?";
            $params[] = $filters['sale_type'];
        }

        if (!empty($filters['search'])) {
            $term = '%' . trim($filters['search']) . '%';
            $sql .= " AND (l.company_name LIKE ? OR l.contact_person LIKE ? OR l.lead_number LIKE ? OR l.city LIKE ?)";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $sql .= " GROUP BY l.id ORDER BY l.id DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createLead(array $data, array $user): array
    {
        // Validate contact phone
        $phone = trim($data['contact_phone'] ?? '');
        $cleanPhone = preg_replace('/[\s\-\(\)\.]/', '', $phone);
        $digitsOnly = ltrim($cleanPhone, '+');
        if (empty($phone)) {
            throw new \InvalidArgumentException("Contact phone number is required.");
        }
        if (strlen($digitsOnly) < 10 || strlen($digitsOnly) > 15 || !preg_match('/^\+?[0-9]{10,15}$/', $cleanPhone)) {
            throw new \InvalidArgumentException("Invalid contact phone number '{$phone}'. Must be a valid 10 to 15 digit telephone or mobile number.");
        }
        if (preg_match('/^(\d)\1+$/', $digitsOnly)) {
            throw new \InvalidArgumentException("Invalid contact phone number '{$phone}'. Repeating dummy digits are not allowed.");
        }

        // Validate contact email if provided
        $email = !empty($data['contact_email']) ? trim($data['contact_email']) : null;
        if ($email !== null && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("Invalid contact email address format: '{$email}'.");
        }

        // Generate lead number
        $year = date('Y');
        $cntStmt = $this->pdo->query("SELECT COUNT(*) FROM sales_leads");
        $nextSeq = str_pad((int)$cntStmt->fetchColumn() + 1, 4, '0', STR_PAD_LEFT);
        $leadNumber = "LD-{$year}-{$nextSeq}";

        $assignedTo = !empty($data['assigned_to_user_id']) ? (int)$data['assigned_to_user_id'] : $user['id'];

        $stmt = $this->pdo->prepare("
            INSERT INTO sales_leads (
                lead_number, company_name, contact_person, contact_email, contact_phone,
                city, state, region, sale_type, product_class, estimated_value,
                source, assigned_to_user_id, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $leadNumber,
            $data['company_name'] ?? 'Untitled Prospect',
            $data['contact_person'] ?? '',
            $data['contact_email'] ?? null,
            $data['contact_phone'] ?? '',
            $data['city'] ?? 'Delhi',
            $data['state'] ?? 'Delhi',
            $data['region'] ?? 'North',
            $data['sale_type'] ?? 'End_Customer',
            $data['product_class'] ?? 'Online UPS',
            (float)($data['estimated_value'] ?? 0.00),
            $data['source'] ?? 'Direct Inquiry',
            $assignedTo,
            $data['status'] ?? 'New'
        ]);

        $newId = (int)$this->pdo->lastInsertId();
        return $this->getLeadById($newId);
    }

    public function getLeadById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT l.*, u.name AS salesperson_name, u.email AS salesperson_email
            FROM sales_leads l
            LEFT JOIN users u ON l.assigned_to_user_id = u.id
            WHERE l.id = ?
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function updateLeadStatus(int $id, string $status, ?string $lostReason = null): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE sales_leads
            SET status = ?, lost_reason = ?
            WHERE id = ?
        ");
        return $stmt->execute([$status, $lostReason, $id]);
    }

    // =========================================================================
    // 2. SALES FIELD VISITS (WITH GPS COORDINATES)
    // =========================================================================

    public function getVisits(array $filters, array $user): array
    {
        $sql = "SELECT v.*, u.name AS salesperson_name
                FROM sales_visits v
                LEFT JOIN users u ON v.salesperson_id = u.id
                WHERE 1=1";
        $params = [];

        if (($user['scope'] ?? 'own') === 'own' && ($user['level'] ?? 'L1') === 'L1' && ($user['role_type'] ?? '') === 'Sales') {
            $sql .= " AND v.salesperson_id = ?";
            $params[] = $user['id'];
        }

        $sql .= " ORDER BY v.visit_date DESC, v.check_in_time DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createVisit(array $data, array $user): array
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO sales_visits (
                lead_id, customer_name, salesperson_id, visit_date,
                check_in_time, check_out_time, latitude, longitude,
                location_name, contact_person_met, discussion_notes, next_follow_up
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            !empty($data['lead_id']) ? (int)$data['lead_id'] : null,
            $data['customer_name'] ?? 'Client Office',
            $user['id'],
            $data['visit_date'] ?? date('Y-m-d'),
            $data['check_in_time'] ?? date('Y-m-d H:i:s'),
            !empty($data['check_out_time']) ? $data['check_out_time'] : null,
            !empty($data['latitude']) ? (float)$data['latitude'] : null,
            !empty($data['longitude']) ? (float)$data['longitude'] : null,
            $data['location_name'] ?? 'Client Site',
            $data['contact_person_met'] ?? '',
            $data['discussion_notes'] ?? '',
            !empty($data['next_follow_up']) ? $data['next_follow_up'] : null,
        ]);

        $id = (int)$this->pdo->lastInsertId();
        $stmt = $this->pdo->prepare("SELECT * FROM sales_visits WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // =========================================================================
    // 3. QUOTATION BUILDER & BELOW-MSP PRICING GATE
    // =========================================================================

    public function getQuotes(array $filters, array $user): array
    {
        $sql = "SELECT q.*, u.name AS salesperson_name, approver.name AS approved_by_name,
                       l.lead_number, l.company_name AS lead_company_name,
                       COALESCE(q.contact_person, l.contact_person) AS contact_person,
                       COALESCE(q.contact_phone, l.contact_phone) AS contact_phone,
                       COALESCE(q.contact_email, l.contact_email) AS contact_email
                FROM sales_quotations q
                LEFT JOIN users u ON q.salesperson_id = u.id
                LEFT JOIN users approver ON q.approved_by_user_id = approver.id
                LEFT JOIN sales_leads l ON q.lead_id = l.id
                WHERE 1=1";
        $params = [];

        if (($user['scope'] ?? 'own') === 'own' && ($user['level'] ?? 'L1') === 'L1' && ($user['role_type'] ?? '') === 'Sales') {
            $sql .= " AND q.salesperson_id = ?";
            $params[] = $user['id'];
        }

        if (!empty($filters['approval_status'])) {
            $sql .= " AND q.approval_status = ?";
            $params[] = $filters['approval_status'];
        }

        if (!empty($filters['lead_id'])) {
            $sql .= " AND q.lead_id = ?";
            $params[] = (int)$filters['lead_id'];
        }

        $sql .= " ORDER BY q.id DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $quotes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch line items for each quote
        foreach ($quotes as &$quote) {
            $itemStmt = $this->pdo->prepare("SELECT * FROM quotation_items WHERE quotation_id = ?");
            $itemStmt->execute([$quote['id']]);
            $quote['items'] = $itemStmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return $quotes;
    }

    public function createQuote(array $data, array $user): array
    {
        $items = $data['items'] ?? [];
        if (empty($items)) {
            throw new Exception("Quotation must contain at least one line item.");
        }

        // Generate Quote number
        $year = date('Y');
        $cntStmt = $this->pdo->query("SELECT COUNT(*) FROM sales_quotations");
        $nextSeq = str_pad((int)$cntStmt->fetchColumn() + 1, 4, '0', STR_PAD_LEFT);
        $quoteNumber = "QUO-{$year}-{$nextSeq}";

        // Tally items economics
        $subtotalAmount = 0.00;
        $totalCostPrice = 0.00;
        $totalMspFloor = 0.00;

        foreach ($items as $item) {
            $qty = max(1, (int)($item['quantity'] ?? 1));
            $unitCost = (float)($item['unit_cost_price'] ?? 0.00);
            $unitMsp = (float)($item['unit_msp_floor'] ?? 0.00);
            $quotedUnit = (float)($item['quoted_unit_price'] ?? 0.00);
            $extWarrantyPrice = (float)($item['extended_warranty_price'] ?? 0.00);

            $lineTotal = ($quotedUnit + $extWarrantyPrice) * $qty;
            $subtotalAmount += $lineTotal;
            $totalCostPrice += ($unitCost * $qty);
            $totalMspFloor += ($unitMsp * $qty);
        }

        // Add separate Services / Commissioning & AMC data
        $installationCharges = max(0.00, (float)($data['installation_charges'] ?? 0.00));
        $amcCharges = max(0.00, (float)($data['amc_charges'] ?? 0.00));
        $subtotalAmount += ($installationCharges + $amcCharges);

        // Parse Discount: Auto-detect percentage if '%' is present, or flat integer/float
        $discountInput = trim((string)($data['discount_input'] ?? $data['discount_amount'] ?? '0'));
        $discount = 0.00;
        if (strpos($discountInput, '%') !== false) {
            $pctVal = (float)str_replace(['%', ' '], '', $discountInput);
            $discount = round($subtotalAmount * ($pctVal / 100), 2);
        } else {
            $discount = max(0.00, (float)str_replace(',', '', $discountInput));
        }

        $finalQuoteAmount = max(0.00, $subtotalAmount - $discount);

        $grossMarginPercent = ($finalQuoteAmount > 0)
            ? round((($finalQuoteAmount - $totalCostPrice) / $finalQuoteAmount) * 100, 2)
            : 0.00;

        // Below-MSP floor check
        $isBelowMsp = ($finalQuoteAmount < $totalMspFloor) ? 1 : 0;
        $shortfall = $isBelowMsp ? round($totalMspFloor - $finalQuoteAmount, 2) : 0.00;

        // Auto-approve if >= MSP; escalate to VP if below MSP
        $approvalStatus = $isBelowMsp ? 'Pending_VP_Approval' : 'Auto_Approved';

        $stmt = $this->pdo->prepare("
            INSERT INTO sales_quotations (
                quote_number, quote_date, lead_id, customer_name, contact_person, contact_phone, contact_email,
                sale_type, salesperson_id, version, subtotal_amount, discount_amount, discount_input,
                installation_charges, amc_charges, total_cost_price,
                total_msp_floor, final_quote_amount, gross_margin_percent,
                is_below_msp, msp_shortfall_amount, approval_status, approval_remarks,
                valid_until
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $quoteNumber,
            !empty($data['quote_date']) ? $data['quote_date'] : date('Y-m-d'),
            !empty($data['lead_id']) ? (int)$data['lead_id'] : null,
            $data['customer_name'] ?? 'Direct Customer',
            $data['contact_person'] ?? null,
            $data['contact_phone'] ?? null,
            $data['contact_email'] ?? null,
            $data['sale_type'] ?? 'End_Customer',
            $user['id'],
            'v1.0',
            $subtotalAmount,
            $discount,
            $discountInput,
            $installationCharges,
            $amcCharges,
            $totalCostPrice,
            $totalMspFloor,
            $finalQuoteAmount,
            $grossMarginPercent,
            $isBelowMsp,
            $shortfall,
            $approvalStatus,
            $data['approval_remarks'] ?? null,
            $data['valid_until'] ?? date('Y-m-d', strtotime('+30 days'))
        ]);

        $quotationId = (int)$this->pdo->lastInsertId();

        // Insert equipment items
        $itemStmt = $this->pdo->prepare("
            INSERT INTO quotation_items (
                quotation_id, product_id, product_name, product_sku,
                warranty_terms, extended_warranty, extended_warranty_price,
                item_type, quantity, unit_cost_price, unit_msp_floor,
                quoted_unit_price, total_price
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        foreach ($items as $item) {
            $qty = max(1, (int)($item['quantity'] ?? 1));
            $quotedUnit = (float)($item['quoted_unit_price'] ?? 0.00);
            $extWarrantyPrice = (float)($item['extended_warranty_price'] ?? 0.00);
            $lineTotal = ($quotedUnit + $extWarrantyPrice) * $qty;

            $itemStmt->execute([
                $quotationId,
                !empty($item['product_id']) ? (int)$item['product_id'] : null,
                $item['product_name'] ?? 'Custom UPS System',
                $item['product_sku'] ?? 'AVO-CUSTOM',
                $item['warranty_terms'] ?? '2 Years Standard OEM',
                $item['extended_warranty'] ?? 'None',
                $extWarrantyPrice,
                'Equipment',
                $qty,
                (float)($item['unit_cost_price'] ?? 0.00),
                (float)($item['unit_msp_floor'] ?? 0.00),
                $quotedUnit,
                $lineTotal
            ]);
        }

        return $this->getQuoteById($quotationId);
    }

    public function updateQuote(int $id, array $data, array $user): array
    {
        $existing = $this->getQuoteById($id);
        if (!$existing) {
            throw new Exception("Quotation #{$id} not found.");
        }

        $items = $data['items'] ?? [];
        if (empty($items)) {
            throw new Exception("Quotation must contain at least one line item.");
        }

        // Tally items economics
        $subtotalAmount = 0.00;
        $totalCostPrice = 0.00;
        $totalMspFloor = 0.00;

        foreach ($items as $item) {
            $qty = max(1, (int)($item['quantity'] ?? 1));
            $unitCost = (float)($item['unit_cost_price'] ?? 0.00);
            $unitMsp = (float)($item['unit_msp_floor'] ?? 0.00);
            $quotedUnit = (float)($item['quoted_unit_price'] ?? 0.00);
            $extWarrantyPrice = (float)($item['extended_warranty_price'] ?? 0.00);

            $lineTotal = ($quotedUnit + $extWarrantyPrice) * $qty;
            $subtotalAmount += $lineTotal;
            $totalCostPrice += ($unitCost * $qty);
            $totalMspFloor += ($unitMsp * $qty);
        }

        // Add separate Services / Commissioning & AMC data
        $installationCharges = max(0.00, (float)($data['installation_charges'] ?? 0.00));
        $amcCharges = max(0.00, (float)($data['amc_charges'] ?? 0.00));
        $subtotalAmount += ($installationCharges + $amcCharges);

        // Parse Discount: Auto-detect percentage if '%' is present, or flat integer/float
        $discountInput = trim((string)($data['discount_input'] ?? $data['discount_amount'] ?? '0'));
        $discount = 0.00;
        if (strpos($discountInput, '%') !== false) {
            $pctVal = (float)str_replace(['%', ' '], '', $discountInput);
            $discount = round($subtotalAmount * ($pctVal / 100), 2);
        } else {
            $discount = max(0.00, (float)str_replace(',', '', $discountInput));
        }

        $finalQuoteAmount = max(0.00, $subtotalAmount - $discount);

        $grossMarginPercent = ($finalQuoteAmount > 0)
            ? round((($finalQuoteAmount - $totalCostPrice) / $finalQuoteAmount) * 100, 2)
            : 0.00;

        // Below-MSP floor check
        $isBelowMsp = ($finalQuoteAmount < $totalMspFloor) ? 1 : 0;
        $shortfall = $isBelowMsp ? round($totalMspFloor - $finalQuoteAmount, 2) : 0.00;

        // Auto-approve if >= MSP; escalate to VP if below MSP
        $approvalStatus = $isBelowMsp ? 'Pending_VP_Approval' : 'Auto_Approved';
        if ($isBelowMsp && !empty($existing['approved_by_user_id']) && $existing['approval_status'] === 'Approved') {
            // If already approved by VP and still below MSP, keep Approved status
            $approvalStatus = 'Approved';
        }

        $stmt = $this->pdo->prepare("
            UPDATE sales_quotations SET
                lead_id = ?,
                quote_date = ?,
                customer_name = ?,
                contact_person = ?,
                contact_phone = ?,
                contact_email = ?,
                sale_type = ?,
                subtotal_amount = ?,
                discount_amount = ?,
                discount_input = ?,
                installation_charges = ?,
                amc_charges = ?,
                total_cost_price = ?,
                total_msp_floor = ?,
                final_quote_amount = ?,
                gross_margin_percent = ?,
                is_below_msp = ?,
                msp_shortfall_amount = ?,
                approval_status = ?,
                approval_remarks = ?,
                valid_until = ?
            WHERE id = ?
        ");

        $stmt->execute([
            !empty($data['lead_id']) ? (int)$data['lead_id'] : null,
            !empty($data['quote_date']) ? $data['quote_date'] : ($existing['quote_date'] ?? date('Y-m-d')),
            $data['customer_name'] ?? $existing['customer_name'],
            $data['contact_person'] ?? $existing['contact_person'],
            $data['contact_phone'] ?? $existing['contact_phone'],
            $data['contact_email'] ?? $existing['contact_email'],
            $data['sale_type'] ?? $existing['sale_type'],
            $subtotalAmount,
            $discount,
            $discountInput,
            $installationCharges,
            $amcCharges,
            $totalCostPrice,
            $totalMspFloor,
            $finalQuoteAmount,
            $grossMarginPercent,
            $isBelowMsp,
            $shortfall,
            $approvalStatus,
            $data['approval_remarks'] ?? $existing['approval_remarks'],
            $data['valid_until'] ?? $existing['valid_until'],
            $id
        ]);

        // Replace line items
        $delStmt = $this->pdo->prepare("DELETE FROM quotation_items WHERE quotation_id = ?");
        $delStmt->execute([$id]);

        $itemStmt = $this->pdo->prepare("
            INSERT INTO quotation_items (
                quotation_id, product_id, product_name, product_sku,
                warranty_terms, extended_warranty, extended_warranty_price,
                item_type, quantity, unit_cost_price, unit_msp_floor,
                quoted_unit_price, total_price
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        foreach ($items as $item) {
            $qty = max(1, (int)($item['quantity'] ?? 1));
            $quotedUnit = (float)($item['quoted_unit_price'] ?? 0.00);
            $extWarrantyPrice = (float)($item['extended_warranty_price'] ?? 0.00);
            $lineTotal = ($quotedUnit + $extWarrantyPrice) * $qty;

            $itemStmt->execute([
                $id,
                !empty($item['product_id']) ? (int)$item['product_id'] : null,
                $item['product_name'] ?? 'Custom UPS System',
                $item['product_sku'] ?? 'AVO-CUSTOM',
                $item['warranty_terms'] ?? '2 Years Standard OEM',
                $item['extended_warranty'] ?? 'None',
                $extWarrantyPrice,
                'Equipment',
                $qty,
                (float)($item['unit_cost_price'] ?? 0.00),
                (float)($item['unit_msp_floor'] ?? 0.00),
                $quotedUnit,
                $lineTotal
            ]);
        }

        return $this->getQuoteById($id);
    }

    public function getQuoteById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT q.*, u.name AS salesperson_name, approver.name AS approved_by_name,
                   l.lead_number, l.company_name AS lead_company_name,
                   COALESCE(q.contact_person, l.contact_person) AS contact_person,
                   COALESCE(q.contact_phone, l.contact_phone) AS contact_phone,
                   COALESCE(q.contact_email, l.contact_email) AS contact_email
            FROM sales_quotations q
            LEFT JOIN users u ON q.salesperson_id = u.id
            LEFT JOIN users approver ON q.approved_by_user_id = approver.id
            LEFT JOIN sales_leads l ON q.lead_id = l.id
            WHERE q.id = ?
        ");
        $stmt->execute([$id]);
        $quote = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$quote) return null;

        $itemStmt = $this->pdo->prepare("SELECT * FROM quotation_items WHERE quotation_id = ?");
        $itemStmt->execute([$id]);
        $quote['items'] = $itemStmt->fetchAll(PDO::FETCH_ASSOC);

        return $quote;
    }

    public function approveQuote(int $id, bool $approved, ?string $remarks, array $user): array
    {
        $status = $approved ? 'Approved' : 'Rejected';
        $stmt = $this->pdo->prepare("
            UPDATE sales_quotations
            SET approval_status = ?, approved_by_user_id = ?, approval_remarks = ?
            WHERE id = ?
        ");
        $stmt->execute([$status, $user['id'], $remarks, $id]);
        return $this->getQuoteById($id);
    }

    public function convertQuoteToSO(int $id, array $user): array
    {
        $quote = $this->getQuoteById($id);
        if (!$quote) {
            throw new Exception("Quotation not found.");
        }

        if (!empty($quote['converted_order_number'])) {
            throw new Exception("Quotation {$quote['quote_number']} has already been converted to Sales Order {$quote['converted_order_number']}.");
        }

        if (!in_array($quote['approval_status'], ['Auto_Approved', 'Approved'])) {
            throw new Exception("Quotation must be Approved or Auto-Approved before converting to Sales Order.");
        }

        // Generate SO number
        $soNumber = "SO-" . date('Ymd') . "-" . strtoupper(substr(md5(uniqid()), 0, 4));

        // Create Order
        $stmt = $this->pdo->prepare("
            INSERT INTO orders (
                order_number, customer_id, customer_name, status,
                subtotal, tax_amount, discount_amount, grand_total,
                created_by_id, created_by_name, notes
            ) VALUES (?, ?, ?, 'approved', ?, ?, ?, ?, ?, ?, ?)
        ");

        $subtotal = (float)$quote['subtotal_amount'];
        $discount = (float)$quote['discount_amount'];
        $tax = round(($subtotal - $discount) * 0.18, 2); // 18% GST standard
        $grandTotal = ($subtotal - $discount) + $tax;

        $stmt->execute([
            $soNumber,
            $quote['lead_id'] ?: 1,
            $quote['customer_name'],
            $subtotal,
            $tax,
            $discount,
            $grandTotal,
            $user['id'],
            $user['name'],
            "Converted from Quotation {$quote['quote_number']}"
        ]);

        $orderId = (int)$this->pdo->lastInsertId();

        // Create Order Items
        $oItemStmt = $this->pdo->prepare("
            INSERT INTO order_items (
                order_id, product_id, product_name, sku,
                quantity, unit_price, tax_rate, tax_amount, line_total
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        foreach ($quote['items'] as $item) {
            $qty = (int)$item['quantity'];
            $price = (float)$item['quoted_unit_price'];
            $lineTax = round(($price * $qty) * 0.18, 2);
            $lineTotal = ($price * $qty) + $lineTax;

            $oItemStmt->execute([
                $orderId,
                $item['product_id'] ?: 1,
                $item['product_name'],
                $item['product_sku'] ?: 'AVO-PROD',
                $qty,
                $price,
                18.00,
                $lineTax,
                $lineTotal
            ]);
        }

        // Record conversion on sales_quotations
        $updateQStmt = $this->pdo->prepare("
            UPDATE sales_quotations
            SET converted_order_id = ?, converted_order_number = ?
            WHERE id = ?
        ");
        $updateQStmt->execute([$orderId, $soNumber, $id]);

        return [
            'order_id' => $orderId,
            'order_number' => $soNumber,
            'quote_id' => $id,
            'quote_number' => $quote['quote_number'],
            'customer_name' => $quote['customer_name'],
            'grand_total' => $grandTotal,
            'message' => "Successfully converted Quotation {$quote['quote_number']} to Sales Order {$soNumber}."
        ];
    }

    // =========================================================================
    // 4. TENDER REGISTER & BANK GUARANTEES (PER CLIENT PDF SPEC)
    // =========================================================================

    public function getTenders(array $filters): array
    {
        $sql = "SELECT t.*, u.name AS assigned_officer_name
                FROM tender_register t
                LEFT JOIN users u ON t.assigned_officer_id = u.id
                WHERE 1=1";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND t.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['authority_type'])) {
            $sql .= " AND t.authority_type = ?";
            $params[] = $filters['authority_type'];
        }

        $sql .= " ORDER BY t.bid_submission_deadline ASC, t.id DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $tenders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($tenders as &$t) {
            $gStmt = $this->pdo->prepare("SELECT * FROM tender_guarantees WHERE tender_id = ?");
            $gStmt->execute([$t['id']]);
            $t['guarantees'] = $gStmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return $tenders;
    }

    public function createTender(array $data, array $user): array
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO tender_register (
                tender_ref_no, issuing_authority, authority_type, tender_title,
                estimated_value, published_date, bid_submission_deadline,
                quote_strategy, allocated_dealer_name, assigned_officer_id,
                status, avo_quoted_price, outcome_remarks
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $data['tender_ref_no'] ?? ('TND-' . strtoupper(substr(md5(uniqid()), 0, 8))),
            $data['issuing_authority'] ?? 'Government Entity',
            $data['authority_type'] ?? 'PSU',
            $data['tender_title'] ?? 'UPS Power Backup System Tender',
            (float)($data['estimated_value'] ?? 0.00),
            $data['published_date'] ?? date('Y-m-d'),
            $data['bid_submission_deadline'] ?? date('Y-m-d 17:00:00', strtotime('+15 days')),
            $data['quote_strategy'] ?? 'Direct_AVO',
            $data['allocated_dealer_name'] ?? null,
            !empty($data['assigned_officer_id']) ? (int)$data['assigned_officer_id'] : $user['id'],
            $data['status'] ?? 'Identified',
            !empty($data['avo_quoted_price']) ? (float)$data['avo_quoted_price'] : null,
            $data['outcome_remarks'] ?? null
        ]);

        $tenderId = (int)$this->pdo->lastInsertId();

        // Optional initial guarantee
        if (!empty($data['guarantee_amount']) && (float)$data['guarantee_amount'] > 0) {
            $gStmt = $this->pdo->prepare("
                INSERT INTO tender_guarantees (
                    tender_id, guarantee_type, amount, issuing_bank,
                    bg_number, effective_date, expiry_date, claim_expiry_date, status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Active')
            ");
            $gStmt->execute([
                $tenderId,
                $data['guarantee_type'] ?? 'EMD_BG',
                (float)$data['guarantee_amount'],
                $data['issuing_bank'] ?? 'Nationalized Bank',
                $data['bg_number'] ?? 'BG-' . strtoupper(substr(md5(uniqid()), 0, 6)),
                $data['effective_date'] ?? date('Y-m-d'),
                $data['expiry_date'] ?? date('Y-m-d', strtotime('+90 days')),
                $data['claim_expiry_date'] ?? date('Y-m-d', strtotime('+120 days'))
            ]);
        }

        $stmt = $this->pdo->prepare("SELECT * FROM tender_register WHERE id = ?");
        $stmt->execute([$tenderId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateTenderOutcome(int $id, array $data): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE tender_register
            SET status = ?, l1_vendor_name = ?, l1_price = ?, outcome_remarks = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['status'] ?? 'Awarded',
            $data['l1_vendor_name'] ?? null,
            !empty($data['l1_price']) ? (float)$data['l1_price'] : null,
            $data['outcome_remarks'] ?? null,
            $id
        ]);
    }

    public function getGuarantees(): array
    {
        $stmt = $this->pdo->query("
            SELECT g.*, t.tender_ref_no, t.issuing_authority, t.tender_title
            FROM tender_guarantees g
            JOIN tender_register t ON g.tender_id = t.id
            ORDER BY g.expiry_date ASC
        ");
        $guarantees = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $today = new \DateTime();
        foreach ($guarantees as &$g) {
            $exp = new \DateTime($g['expiry_date']);
            $diffDays = (int)$today->diff($exp)->format('%r%a');
            $g['days_remaining'] = $diffDays;

            if ($diffDays < 0) {
                $g['alert_status'] = 'EXPIRED';
            } elseif ($diffDays <= 7) {
                $g['alert_status'] = 'CRITICAL_7D';
            } elseif ($diffDays <= 15) {
                $g['alert_status'] = 'WARNING_15D';
            } elseif ($diffDays <= 30) {
                $g['alert_status'] = 'CAUTION_30D';
            } else {
                $g['alert_status'] = 'NORMAL';
            }
        }

        return $guarantees;
    }

    // =========================================================================
    // 5. SI MULTI-BID CLUSTERING (HIT RATE DEDUPLICATION)
    // =========================================================================

    public function getClusters(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM si_rfp_clusters ORDER BY id DESC");
        $clusters = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $totalClusteredDeals = count($clusters);
        $totalWonDeals = 0;
        $totalRawBids = 0;
        $totalRawWins = 0;

        foreach ($clusters as &$cluster) {
            $bStmt = $this->pdo->prepare("SELECT * FROM si_rfp_bids WHERE cluster_id = ?");
            $bStmt->execute([$cluster['id']]);
            $bids = $bStmt->fetchAll(PDO::FETCH_ASSOC);
            $cluster['bids'] = $bids;

            $bidsCount = count($bids);
            $totalRawBids += $bidsCount;

            if ($cluster['cluster_status'] === 'Deal_Won') {
                $totalWonDeals++;
                $totalRawWins += 1; // 1 winning SI out of N bidding SIs
            }
        }

        $clusteredHitRate = ($totalClusteredDeals > 0)
            ? round(($totalWonDeals / $totalClusteredDeals) * 100, 1)
            : 0.0;

        $dilutedRawHitRate = ($totalRawBids > 0)
            ? round(($totalRawWins / $totalRawBids) * 100, 1)
            : 0.0;

        return [
            'clusters' => $clusters,
            'analytics' => [
                'total_clusters' => $totalClusteredDeals,
                'deals_won' => $totalWonDeals,
                'clustered_hit_rate_pct' => $clusteredHitRate,
                'total_raw_si_bids' => $totalRawBids,
                'diluted_raw_hit_rate_pct' => $dilutedRawHitRate,
            ]
        ];
    }

    public function createCluster(array $data): array
    {
        $code = 'CLUST-' . strtoupper(substr(md5(uniqid()), 0, 8));
        $stmt = $this->pdo->prepare("
            INSERT INTO si_rfp_clusters (
                cluster_code, end_client_name, project_scope,
                est_total_deal_value, cluster_status
            ) VALUES (?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $code,
            $data['end_client_name'] ?? 'Enterprise Client',
            $data['project_scope'] ?? 'Data Center Backup UPS',
            (float)($data['est_total_deal_value'] ?? 0.00),
            $data['cluster_status'] ?? 'Active_Bidding'
        ]);

        $clusterId = (int)$this->pdo->lastInsertId();

        if (!empty($data['bids']) && is_array($data['bids'])) {
            $bStmt = $this->pdo->prepare("
                INSERT INTO si_rfp_bids (
                    cluster_id, si_company_name, salesperson_name, quoted_amount, bid_status
                ) VALUES (?, ?, ?, ?, ?)
            ");
            foreach ($data['bids'] as $bid) {
                $bStmt->execute([
                    $clusterId,
                    $bid['si_company_name'] ?? 'SI Partner',
                    $bid['salesperson_name'] ?? 'Sales Rep',
                    (float)($bid['quoted_amount'] ?? 0.00),
                    $bid['bid_status'] ?? 'Submitted'
                ]);
            }
        }

        return ['id' => $clusterId, 'cluster_code' => $code];
    }

    // =========================================================================
    // 6. ORDER MILESTONE TRACKER & AUDIT PACKAGE
    // =========================================================================

    public function getOrderMilestones(array $user): array
    {
        $sql = "SELECT o.*, 
                    inv.invoice_number, inv.status AS invoice_status, inv.due_date,
                    u.name AS rep_name
                FROM orders o
                LEFT JOIN invoices inv ON o.id = inv.order_id
                LEFT JOIN users u ON o.created_by_id = u.id
                ORDER BY o.id DESC";

        $stmt = $this->pdo->query($sql);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($orders as &$ord) {
            // Calculate 6-stage milestone progress
            // Stages: 1. SO Raised -> 2. Picked -> 3. Dispatched -> 4. Invoiced -> 5. Delivered -> 6. Commissioned
            $stages = [
                ['key' => 'so_raised', 'title' => 'SO Raised', 'completed' => true, 'date' => $ord['created_at']],
                ['key' => 'allocated', 'title' => 'Stock Allocated', 'completed' => in_array($ord['status'], ['approved', 'shipped', 'delivered', 'completed']), 'date' => $ord['updated_at']],
                ['key' => 'dispatched', 'title' => 'Dispatched', 'completed' => in_array($ord['status'], ['shipped', 'delivered', 'completed']), 'date' => in_array($ord['status'], ['shipped', 'delivered', 'completed']) ? $ord['updated_at'] : null],
                ['key' => 'invoiced', 'title' => 'Tally Invoiced', 'completed' => !empty($ord['invoice_number']), 'date' => !empty($ord['invoice_number']) ? $ord['created_at'] : null],
                ['key' => 'delivered', 'title' => 'POD Delivered', 'completed' => in_array($ord['status'], ['delivered', 'completed']), 'date' => in_array($ord['status'], ['delivered', 'completed']) ? $ord['updated_at'] : null],
                ['key' => 'commissioned', 'title' => 'FSR Signed', 'completed' => ($ord['status'] === 'completed'), 'date' => ($ord['status'] === 'completed') ? $ord['updated_at'] : null],
            ];
            $ord['milestone_stages'] = $stages;
        }

        return $orders;
    }

    public function getAuditPackage(int $orderId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            throw new Exception("Order not found.");
        }

        return [
            'order_id' => $orderId,
            'order_number' => $order['order_number'],
            'customer_name' => $order['customer_name'],
            'documents' => [
                ['name' => "Tax_Invoice_{$order['order_number']}.pdf", 'type' => 'Invoice', 'status' => 'Verified in Tally', 'size_kb' => 142],
                ['name' => "Proof_Of_Delivery_POD_{$order['order_number']}.pdf", 'type' => 'POD', 'status' => 'Signed by Consignee', 'size_kb' => 285],
                ['name' => "Commissioning_FSR_Report_{$order['order_number']}.pdf", 'type' => 'FSR', 'status' => 'Engineer On-Screen Signed', 'size_kb' => 310]
            ],
            'download_bundle_name' => "AVO_Audit_Package_{$order['order_number']}.zip"
        ];
    }
}
