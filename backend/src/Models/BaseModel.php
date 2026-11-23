<?php

namespace App\Models;

use App\Core\Database;
use PDO;

abstract class BaseModel
{
    protected static string $table;
    protected static string $primaryKey = 'id';

    public static function getPdo(): PDO
    {
        return Database::getConnection();
    }

    public static function find(int $id): ?array
    {
        $stmt = self::getPdo()->prepare("SELECT * FROM " . static::$table . " WHERE " . static::$primaryKey . " = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public static function all(string $orderBy = 'id DESC'): array
    {
        $stmt = self::getPdo()->query("SELECT * FROM " . static::$table . " ORDER BY {$orderBy}");
        return $stmt->fetchAll();
    }

    public static function paginate(int $page = 1, int $limit = 10, string $where = '1=1', array $params = [], string $orderBy = 'id DESC'): array
    {
        $offset = ($page - 1) * $limit;
        $pdo = self::getPdo();

        $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM " . static::$table . " WHERE {$where}");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetch()['total'];

        $dataStmt = $pdo->prepare("SELECT * FROM " . static::$table . " WHERE {$where} ORDER BY {$orderBy} LIMIT :limit OFFSET :offset");
        foreach ($params as $k => $v) {
            $dataStmt->bindValue($k, $v);
        }
        $dataStmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $dataStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $dataStmt->execute();
        $items = $dataStmt->fetchAll();

        return [
            'data' => $items,
            'pagination' => [
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'total_pages' => (int) ceil($total / $limit),
            ],
        ];
    }

    public static function insert(array $data): int
    {
        $pdo = self::getPdo();
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));

        $sql = "INSERT INTO " . static::$table . " ({$columns}) VALUES ({$placeholders})";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($data);

        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $fields = [];
        foreach (array_keys($data) as $col) {
            $fields[] = "{$col} = :{$col}";
        }
        $fieldsStr = implode(', ', $fields);

        $data[static::$primaryKey] = $id;
        $sql = "UPDATE " . static::$table . " SET {$fieldsStr} WHERE " . static::$primaryKey . " = :" . static::$primaryKey;
        $stmt = self::getPdo()->prepare($sql);
        return $stmt->execute($data);
    }

    public static function delete(int $id): bool
    {
        $stmt = self::getPdo()->prepare("DELETE FROM " . static::$table . " WHERE " . static::$primaryKey . " = :id");
        return $stmt->execute(['id' => $id]);
    }
}

