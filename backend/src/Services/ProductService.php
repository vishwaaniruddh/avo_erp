<?php

namespace App\Services;

use App\Core\Cache\CacheManager;
use App\Models\AuditLog;
use App\Models\Product;

class ProductService
{
    private const CACHE_KEY_ALL = 'products:all';

    public static function getAll(): array
    {
        $cached = CacheManager::get(self::CACHE_KEY_ALL);
        if ($cached !== null) {
            return $cached;
        }

        $products = Product::all('id ASC');
        CacheManager::set(self::CACHE_KEY_ALL, $products, 300); // 5 min TTL
        return $products;
    }

    public static function getPaginated(int $page = 1, int $limit = 10, string $search = ''): array
    {
        $where = '1=1';
        $params = [];

        if (!empty($search)) {
            $where .= ' AND (name LIKE :search OR sku LIKE :search OR category LIKE :search)';
            $params[':search'] = "%{$search}%";
        }

        return Product::paginate($page, $limit, $where, $params, 'id ASC');
    }

    public static function getById(int $id): ?array
    {
        return Product::find($id);
    }

    public static function create(array $data, array $user): array
    {
        // Validation
        if (empty($data['sku']) || empty($data['name'])) {
            throw new \InvalidArgumentException('Product SKU and Name are required');
        }

        if (Product::findBySku($data['sku'])) {
            throw new \InvalidArgumentException("Product with SKU '{$data['sku']}' already exists");
        }

        $insertData = [
            'sku' => trim($data['sku']),
            'name' => trim($data['name']),
            'description' => $data['description'] ?? null,
            'category' => $data['category'] ?? 'General',
            'cost_price' => max(0, (float) ($data['cost_price'] ?? 0)),
            'selling_price' => max(0, (float) ($data['selling_price'] ?? 0)),
            'tax_rate' => max(0, (float) ($data['tax_rate'] ?? 10.0)),
            'stock_quantity' => max(0, (int) ($data['stock_quantity'] ?? 0)),
            'reorder_level' => max(0, (int) ($data['reorder_level'] ?? 10)),
            'unit' => $data['unit'] ?? 'pcs',
        ];

        $id = Product::insert($insertData);
        CacheManager::delete(self::CACHE_KEY_ALL);

        AuditLog::log(
            $user['id'],
            $user['name'],
            $user['role'],
            'CREATE',
            'Product',
            (string) $id,
            null,
            $insertData
        );

        return Product::find($id);
    }

    public static function update(int $id, array $data, array $user): array
    {
        $existing = Product::find($id);
        if (!$existing) {
            throw new \RuntimeException('Product not found');
        }

        $updateData = [];
        $fields = ['name', 'description', 'category', 'cost_price', 'selling_price', 'tax_rate', 'stock_quantity', 'reorder_level', 'unit'];
        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $updateData[$field] = $data[$field];
            }
        }
        $updateData['updated_at'] = date('Y-m-d H:i:s');

        Product::update($id, $updateData);
        CacheManager::delete(self::CACHE_KEY_ALL);

        AuditLog::log(
            $user['id'],
            $user['name'],
            $user['role'],
            'UPDATE',
            'Product',
            (string) $id,
            $existing,
            $updateData
        );

        return Product::find($id);
    }

    public static function updateStock(int $productId, int $quantityDelta, array $user, string $reason = 'Manual adjustment'): array
    {
        $product = Product::find($productId);
        if (!$product) {
            throw new \RuntimeException('Product not found');
        }

        $newStock = $product['stock_quantity'] + $quantityDelta;
        if ($newStock < 0) {
            throw new \InvalidArgumentException("Cannot reduce stock below zero. Current: {$product['stock_quantity']}, requested change: {$quantityDelta}");
        }

        Product::update($productId, [
            'stock_quantity' => $newStock,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        CacheManager::delete(self::CACHE_KEY_ALL);

        AuditLog::log(
            $user['id'],
            $user['name'],
            $user['role'],
            'STOCK_ADJUSTMENT',
            'Product',
            (string) $productId,
            ['old_stock' => $product['stock_quantity']],
            ['new_stock' => $newStock, 'delta' => $quantityDelta, 'reason' => $reason]
        );

        return Product::find($productId);
    }
}

