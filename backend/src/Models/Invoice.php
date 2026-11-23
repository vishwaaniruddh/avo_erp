<?php

namespace App\Models;

class Invoice extends BaseModel
{
    protected static string $table = 'invoices';

    public static function findWithDetails(int $id): ?array
    {
        $invoice = self::find($id);
        if (!$invoice) {
            return null;
        }

        $order = Order::findWithItems($invoice['order_id']);
        $customer = Customer::find($invoice['customer_id']);

        $invoice['order'] = $order;
        $invoice['customer'] = $customer;

        return $invoice;
    }

    public static function findByInvoiceNumber(string $num): ?array
    {
        $stmt = self::getPdo()->prepare("SELECT * FROM invoices WHERE invoice_number = :num LIMIT 1");
        $stmt->execute(['num' => $num]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}

