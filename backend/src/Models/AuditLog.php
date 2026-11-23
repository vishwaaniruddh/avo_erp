<?php

namespace App\Models;

class AuditLog extends BaseModel
{
    protected static string $table = 'audit_logs';

    public static function log(
        ?int $userId,
        string $userName,
        string $userRole,
        string $action,
        string $entityType,
        string $entityId,
        mixed $oldValues = null,
        mixed $newValues = null,
        string $ipAddress = '127.0.0.1'
    ): int {
        return self::insert([
            'user_id' => $userId,
            'user_name' => $userName,
            'user_role' => $userRole,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'old_values' => $oldValues ? json_encode($oldValues) : null,
            'new_values' => $newValues ? json_encode($newValues) : null,
            'ip_address' => $ipAddress,
        ]);
    }
}

