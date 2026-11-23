<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Order;
use InvalidArgumentException;
use RuntimeException;

class InvoiceService
{
    /**
     * Authoritative Invoice Generation (PHP Enforced)
     */
    public static function generateFromOrder(int $orderId, array $user, int $dueDays = 30): array
    {
        $order = Order::findWithItems($orderId);
        if (!$order) {
            throw new RuntimeException("Order not found with ID {$orderId}");
        }

        if ($order['status'] !== OrderService::STATUS_APPROVED) {
            throw new InvalidArgumentException(
                "Authoritative invoice can only be generated for an APPROVED order. Current order status: '{$order['status']}'"
            );
        }

        // Check if invoice already exists
        $stmt = Invoice::getPdo()->prepare("SELECT * FROM invoices WHERE order_id = :order_id LIMIT 1");
        $stmt->execute(['order_id' => $orderId]);
        $existing = $stmt->fetch();
        if ($existing) {
            return Invoice::findWithDetails($existing['id']);
        }

        $customer = Customer::find($order['customer_id']);
        if (!$customer) {
            throw new RuntimeException("Customer not found");
        }

        $invoiceNumber = 'INV-' . date('Ymd') . '-' . str_pad((string)$orderId, 5, '0', STR_PAD_LEFT);
        $dueDate = date('Y-m-d', strtotime("+{$dueDays} days"));
        $issuedAt = date('Y-m-d H:i:s');

        // Authoritative Checksum
        $checksumPayload = "{$invoiceNumber}|{$orderId}|{$customer['id']}|{$order['grand_total']}|{$issuedAt}";
        $checksum = hash('sha256', $checksumPayload);

        $invoiceId = Invoice::insert([
            'invoice_number' => $invoiceNumber,
            'order_id' => $orderId,
            'customer_id' => $customer['id'],
            'customer_name' => $customer['name'],
            'subtotal' => $order['subtotal'],
            'tax_amount' => $order['tax_amount'],
            'total_amount' => $order['grand_total'],
            'status' => 'unpaid',
            'due_date' => $dueDate,
            'checksum' => $checksum,
            'issued_at' => $issuedAt,
            'notes' => "Official invoice generated from Order {$order['order_number']}",
        ]);

        // Transition Order status to 'invoiced'
        Order::update($orderId, [
            'status' => OrderService::STATUS_INVOICED,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        AuditLog::log(
            $user['id'],
            $user['name'],
            $user['role'],
            'GENERATE_INVOICE',
            'Invoice',
            (string) $invoiceId,
            null,
            [
                'invoice_number' => $invoiceNumber,
                'order_id' => $orderId,
                'total_amount' => $order['grand_total'],
                'checksum' => $checksum,
            ]
        );

        return Invoice::findWithDetails($invoiceId);
    }

    public static function markAsPaid(int $invoiceId, array $user): array
    {
        $invoice = Invoice::find($invoiceId);
        if (!$invoice) {
            throw new RuntimeException("Invoice not found");
        }

        if ($invoice['status'] === 'paid') {
            return Invoice::findWithDetails($invoiceId);
        }

        Invoice::update($invoiceId, [
            'status' => 'paid',
            'paid_at' => date('Y-m-d H:i:s'),
        ]);

        // Deduct from customer outstanding balance
        $customer = Customer::find($invoice['customer_id']);
        if ($customer) {
            $newBalance = max(0, (float) $customer['outstanding_balance'] - (float) $invoice['total_amount']);
            Customer::update($customer['id'], [
                'outstanding_balance' => $newBalance,
            ]);
        }

        AuditLog::log(
            $user['id'],
            $user['name'],
            $user['role'],
            'PAY_INVOICE',
            'Invoice',
            (string) $invoiceId,
            ['status' => 'unpaid'],
            ['status' => 'paid', 'paid_amount' => $invoice['total_amount']]
        );

        return Invoice::findWithDetails($invoiceId);
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
            $where .= ' AND (invoice_number LIKE :search OR customer_name LIKE :search)';
            $params[':search'] = "%{$search}%";
        }

        return Invoice::paginate($page, $limit, $where, $params, 'id DESC');
    }
}

