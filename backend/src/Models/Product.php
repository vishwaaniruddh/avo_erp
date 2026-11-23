<?php

namespace App\Models;

class Product extends BaseModel
{
    protected static string $table = 'products';

    public static function findBySku(string $sku): ?array
    {
        $stmt = self::getPdo()->prepare("SELECT * FROM products WHERE sku = :sku LIMIT 1");
        $stmt->execute(['sku' => $sku]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function getLowStock(): array
    {
        $stmt = self::getPdo()->query("SELECT * FROM products WHERE stock_quantity <= reorder_level ORDER BY stock_quantity ASC");
        return $stmt->fetchAll();
    }
}

