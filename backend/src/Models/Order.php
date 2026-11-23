<?php

namespace App\Models;

class Order extends BaseModel
{
    protected static string $table = 'orders';

    public static function findWithItems(int $id): ?array
    {
        $order = self::find($id);
        if (!$order) {
            return null;
        }

        $stmt = self::getPdo()->prepare("SELECT * FROM order_items WHERE order_id = :order_id");
        $stmt->execute(['order_id' => $id]);
        $order['items'] = $stmt->fetchAll();

        return $order;
    }

    public static function findByOrderNumber(string $orderNumber): ?array
    {
        $stmt = self::getPdo()->prepare("SELECT * FROM orders WHERE order_number = :num LIMIT 1");
        $stmt->execute(['num' => $orderNumber]);
        $order = $stmt->fetch();
        if ($order) {
            $itemStmt = self::getPdo()->prepare("SELECT * FROM order_items WHERE order_id = :order_id");
            $itemStmt->execute(['order_id' => $order['id']]);
            $order['items'] = $itemStmt->fetchAll();
        }
        return $order ?: null;
    }
}

