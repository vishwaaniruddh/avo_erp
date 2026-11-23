<?php

namespace App\Controllers;

use App\Core\Request;
use App\Models\AuditLog;

class AuditLogController extends BaseController
{
    public function index(Request $request): void
    {
        $page = max(1, (int) $request->query('page', 1));
        $limit = max(1, min(100, (int) $request->query('limit', 15)));
        $entityType = trim((string) $request->query('entity_type', ''));
        $search = trim((string) $request->query('search', ''));

        $where = '1=1';
        $params = [];

        if (!empty($entityType)) {
            $where .= ' AND entity_type = :entity_type';
            $params[':entity_type'] = $entityType;
        }

        if (!empty($search)) {
            $where .= ' AND (user_name LIKE :search OR action LIKE :search OR entity_id LIKE :search)';
            $params[':search'] = "%{$search}%";
        }

        $paginated = AuditLog::paginate($page, $limit, $where, $params, 'id DESC');
        $this->ok($paginated, 'Audit logs retrieved');
    }
}

