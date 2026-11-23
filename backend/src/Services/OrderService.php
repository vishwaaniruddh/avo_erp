<?php

namespace App\Services;

use App\Core\Cache\CacheManager;
use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Exception;
use InvalidArgumentException;
use RuntimeException;

class OrderService
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING = 'pending_approval';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_INVOICED = 'invoiced';

    public const HIGH_VALUE_THRESHOLD = 1000.00;

    /**
     * Authoritative Final Tax & Total Calculation (PHP Backend)
     */
    public static function calculateAuthoritativeTotals(array $items, float $discount = 0.0): array
    {
        $subtotal = 0.0;
        $totalTax = 0.0;
        $processedItems = [];

        foreach ($items as $item) {
            $qty = (int) ($item['quantity'] ?? 0);
            $unitPrice = (float) ($item['unit_price'] ?? 0.0);
            $taxRate = (float) ($item['tax_rate'] ?? 0.0);

            if ($qty <= 0) {
                throw new InvalidArgumentException("Item quantity must be strictly greater than 0");
            }
            if ($unitPrice < 0) {
                throw new InvalidArgumentException("Item unit price cannot be negative");
            }

            $lineSubtotal = round($qty * $unitPrice, 2);
            $lineTax = round($lineSubtotal * ($taxRate / 100), 2);
            $lineTotal = round($lineSubtotal + $lineTax, 2);

            $subtotal += $lineSubtotal;
            $totalTax += $lineTax;

            $processedItems[] = array_merge($item, [
                'quantity' => $qty,
                'unit_price' => $unitPrice,
                'tax_rate' => $taxRate,
                'tax_amount' => $lineTax,
                'line_total' => $lineTotal,
            ]);
        }

        $discount = max(0, round($discount, 2));
        $grandTotal = max(0, round($subtotal + $totalTax - $discount, 2));

        return [
            'subtotal' => round($subtotal, 2),
            'tax_amount' => round($totalTax, 2),
            'discount_amount' => $discount,
            'grand_total' => $grandTotal,
            'items' => $processedItems,
        ];
    }

    /**
     * Stock Availability Verification (PHP Backend)
     */
    public static function verifyStockAvailability(array $items): array
    {
        $errors = [];
        $verifiedProducts = [];

        foreach ($items as $item) {
            $productId = (int) ($item['product_id'] ?? 0);
            $qtyRequested = (int) ($item['quantity'] ?? 0);

            $product = Product::find($productId);
            if (!$product) {
                $errors[] = "Product with ID {$productId} does not exist";
                continue;
            }

            if ($product['stock_quantity'] < $qtyRequested) {
                $deficit = $qtyRequested - $product['stock_quantity'];
                $errors[] = "Insufficient stock for '{$product['name']}' (SKU: {$product['sku']}). Available: {$product['stock_quantity']}, Requested: {$qtyRequested} (Deficit: {$deficit})";
            } else {
                $verifiedProducts[$productId] = $product;
            }
        }

        if (!empty($errors)) {
            throw new InvalidArgumentException(implode('; ', $errors));
        }

        return $verifiedProducts;
    }

    /**
     * Create Order with authoritative validation, tax calculation, and stock verification
     */
    public static function createOrder(array $data, array $user): array
    {
        $customerId = (int) ($data['customer_id'] ?? 0);
        $customer = Customer::find($customerId);
        if (!$customer) {
            throw new InvalidArgumentException("Invalid customer ID: {$customerId}");
        }

        $rawItems = $data['items'] ?? [];
        if (empty($rawItems)) {
            throw new InvalidArgumentException("An order must contain at least one line item");
        }

        // 1. Verify Stock availability
        $verifiedProducts = self::verifyStockAvailability($rawItems);

        // 2. Attach product snapshot details and authoritative tax rates from DB
        $enrichedItems = [];
        foreach ($rawItems as $rawItem) {
            $p = $verifiedProducts[(int)$rawItem['product_id']];
            $enrichedItems[] = [
                'product_id' => $p['id'],
                'product_name' => $p['name'],
                'sku' => $p['sku'],
                'quantity' => (int) $rawItem['quantity'],
                'unit_price' => (float) ($rawItem['unit_price'] ?? $p['selling_price']),
                'tax_rate' => (float) $p['tax_rate'],
            ];
        }

        // 3. Authoritative Tax & Grand Total Calculation
        $discount = (float) ($data['discount_amount'] ?? 0.0);
        $calc = self::calculateAuthoritativeTotals($enrichedItems, $discount);

        // 4. Business Validation: Customer Credit Limit
        $projectedBalance = (float) $customer['outstanding_balance'] + $calc['grand_total'];
        $creditLimit = (float) $customer['credit_limit'];
        if ($projectedBalance > $creditLimit) {
            $excess = $projectedBalance - $creditLimit;
            throw new InvalidArgumentException(
                "Order grand total ({$calc['grand_total']}) exceeds customer credit limit! " .
                "Credit Limit: {$creditLimit}, Outstanding: {$customer['outstanding_balance']}, Excess: {$excess}"
            );
        }

        // 5. Approval Workflow initial status determination
        // Orders over HIGH_VALUE_THRESHOLD require approval workflow
        $status = ($calc['grand_total'] >= self::HIGH_VALUE_THRESHOLD) 
            ? self::STATUS_PENDING 
            : self::STATUS_APPROVED;

        $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

        $orderId = Order::insert([
            'order_number' => $orderNumber,
            'customer_id' => $customer['id'],
            'customer_name' => $customer['name'],
            'status' => $status,
            'subtotal' => $calc['subtotal'],
            'tax_amount' => $calc['tax_amount'],
            'discount_amount' => $calc['discount_amount'],
            'grand_total' => $calc['grand_total'],
            'created_by_id' => $user['id'],
            'created_by_name' => $user['name'],
            'approved_by_id' => ($status === self::STATUS_APPROVED) ? $user['id'] : null,
            'approved_by_name' => ($status === self::STATUS_APPROVED) ? $user['name'] : null,
            'notes' => $data['notes'] ?? null,
        ]);

        // Insert Order Items
        foreach ($calc['items'] as $item) {
            OrderItem::insert([
                'order_id' => $orderId,
                'product_id' => $item['product_id'],
                'product_name' => $item['product_name'],
                'sku' => $item['sku'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'tax_rate' => $item['tax_rate'],
                'tax_amount' => $item['tax_amount'],
                'line_total' => $item['line_total'],
            ]);

            // If auto-approved, decrement stock right away
            if ($status === self::STATUS_APPROVED) {
                Product::update($item['product_id'], [
                    'stock_quantity' => $verifiedProducts[$item['product_id']]['stock_quantity'] - $item['quantity'],
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }

        if ($status === self::STATUS_APPROVED) {
            Customer::update($customer['id'], [
                'outstanding_balance' => $projectedBalance,
            ]);
            CacheManager::delete('products:all');
        }

        AuditLog::log(
            $user['id'],
            $user['name'],
            $user['role'],
            'CREATE_ORDER',
            'Order',
            (string) $orderId,
            null,
            [
                'order_number' => $orderNumber,
                'status' => $status,
                'grand_total' => $calc['grand_total'],
                'item_count' => count($calc['items']),
            ]
        );

        return Order::findWithItems($orderId);
    }

    /**
     * Approval Workflow Control (PHP Enforced)
     */
    public static function approveOrder(int $orderId, array $user): array
    {
        // Enforce RBAC: Only manager or admin can approve orders
        if (!in_array($user['role'], ['admin', 'manager'])) {
            throw new RuntimeException("Permission denied: Only a Manager or Admin can approve orders");
        }

        $order = Order::findWithItems($orderId);
        if (!$order) {
            throw new RuntimeException("Order not found");
        }

        if ($order['status'] !== self::STATUS_PENDING) {
            throw new InvalidArgumentException("Only orders with 'pending_approval' status can be approved. Current status: {$order['status']}");
        }

        // Re-verify stock before final approval commit
        self::verifyStockAvailability($order['items']);

        // Decrement stock for all items
        foreach ($order['items'] as $item) {
            $product = Product::find($item['product_id']);
            $newStock = $product['stock_quantity'] - $item['quantity'];
            Product::update($product['id'], [
                'stock_quantity' => $newStock,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        // Update customer outstanding balance
        $customer = Customer::find($order['customer_id']);
        Customer::update($customer['id'], [
            'outstanding_balance' => (float) $customer['outstanding_balance'] + (float) $order['grand_total'],
        ]);

        Order::update($orderId, [
            'status' => self::STATUS_APPROVED,
            'approved_by_id' => $user['id'],
            'approved_by_name' => $user['name'],
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        CacheManager::delete('products:all');

        AuditLog::log(
            $user['id'],
            $user['name'],
            $user['role'],
            'APPROVE_ORDER',
            'Order',
            (string) $orderId,
            ['status' => self::STATUS_PENDING],
            ['status' => self::STATUS_APPROVED, 'approved_by' => $user['name']]
        );

        return Order::findWithItems($orderId);
    }

    /**
     * Reject Order Workflow (PHP Enforced)
     */
    public static function rejectOrder(int $orderId, string $reason, array $user): array
    {
        if (!in_array($user['role'], ['admin', 'manager'])) {
            throw new RuntimeException("Permission denied: Only a Manager or Admin can reject orders");
        }

        $order = Order::find($orderId);
        if (!$order) {
            throw new RuntimeException("Order not found");
        }

        if ($order['status'] !== self::STATUS_PENDING) {
            throw new InvalidArgumentException("Only orders with 'pending_approval' status can be rejected");
        }

        Order::update($orderId, [
            'status' => self::STATUS_REJECTED,
            'rejection_reason' => $reason,
            'approved_by_id' => $user['id'],
            'approved_by_name' => $user['name'],
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        AuditLog::log(
            $user['id'],
            $user['name'],
            $user['role'],
            'REJECT_ORDER',
            'Order',
            (string) $orderId,
            ['status' => self::STATUS_PENDING],
            ['status' => self::STATUS_REJECTED, 'reason' => $reason]
        );

        return Order::findWithItems($orderId);
    }

    public static function getPaginated(int $page = 1, int $limit = 10, string $status = '', string $search = ''): array
    {
        $where = '1=1';
        $params = [];

        if (!empty($status)) {
            $where .= ' AND status = :status';
            $params[':status'] = $status;
        }

        if (!empty($search)) {
            $where .= ' AND (order_number LIKE :search OR customer_name LIKE :search)';
            $params[':search'] = "%{$search}%";
        }

        return Order::paginate($page, $limit, $where, $params, 'id DESC');
    }
}

