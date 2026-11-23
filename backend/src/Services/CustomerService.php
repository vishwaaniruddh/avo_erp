<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Customer;

class CustomerService
{
    public static function getAll(): array
    {
        return Customer::all('name ASC');
    }

    public static function getPaginated(int $page = 1, int $limit = 10, string $search = ''): array
    {
        $where = '1=1';
        $params = [];

        if (!empty($search)) {
            $where .= ' AND (name LIKE :search OR email LIKE :search OR company LIKE :search)';
            $params[':search'] = "%{$search}%";
        }

        return Customer::paginate($page, $limit, $where, $params, 'name ASC');
    }

    public static function getById(int $id): ?array
    {
        return Customer::find($id);
    }

    public static function create(array $data, array $user): array
    {
        if (empty($data['name']) || empty($data['email'])) {
            throw new \InvalidArgumentException('Customer Name and Email are required');
        }

        if (Customer::findByEmail($data['email'])) {
            throw new \InvalidArgumentException("Customer with email '{$data['email']}' already exists");
        }

        $insertData = [
            'name' => trim($data['name']),
            'company' => $data['company'] ?? null,
            'email' => trim($data['email']),
            'phone' => $data['phone'] ?? null,
            'tax_number' => $data['tax_number'] ?? null,
            'credit_limit' => max(0, (float) ($data['credit_limit'] ?? 5000.0)),
            'outstanding_balance' => 0.0,
            'address' => $data['address'] ?? null,
        ];

        $id = Customer::insert($insertData);

        AuditLog::log(
            $user['id'],
            $user['name'],
            $user['role'],
            'CREATE',
            'Customer',
            (string) $id,
            null,
            $insertData
        );

        return Customer::find($id);
    }
}

