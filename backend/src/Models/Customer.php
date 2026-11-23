<?php

namespace App\Models;

class Customer extends BaseModel
{
    protected static string $table = 'customers';

    public static function findByEmail(string $email): ?array
    {
        $stmt = self::getPdo()->prepare("SELECT * FROM customers WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}

