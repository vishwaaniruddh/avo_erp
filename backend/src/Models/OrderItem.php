<?php

namespace App\Models;

class OrderItem extends BaseModel
{
    protected static string $table = 'order_items';

    public static function getByOrderId(int $orderId): array
    {
        $stmt = self::getPdo()->prepare("SELECT * FROM order_items WHERE order_id = :order_id");
        $stmt->execute(['order_id' => $orderId]);
        return $stmt->fetchAll();
    }
}

