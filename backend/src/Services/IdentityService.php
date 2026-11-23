<?php

namespace App\Services;

use App\Core\Database;
use App\Models\AuditLog;
use App\Models\User;
use InvalidArgumentException;
use PDO;

class IdentityService
{
    /**
     * 10 Core Role Types defined in SS-34
     */
    public const ROLE_TYPES = [
        'Executive' => [
            'key' => 'Executive',
            'name' => 'Executive & Leadership',
            'screens' => ['Dashboard', 'Orders', 'Invoices', 'Products', 'Customers', 'Bidding', 'Scrap', 'AuditLogs', 'Identity'],
            'description' => 'CEO, MD, COO, Business Heads with full cross-module oversight and signing power.',
        ],
        'Finance' => [
            'key' => 'Finance',
            'name' => 'Finance & Accounts',
            'screens' => ['Dashboard', 'Invoices', 'Orders', 'Customers', 'Scrap', 'AuditLogs'],
            'description' => 'CFO, Credit Controllers, Billing Executives, and Ledger Auditors.',
        ],
        'Sales' => [
            'key' => 'Sales',
            'name' => 'Sales & Commercial Bidding',
            'screens' => ['Dashboard', 'Orders', 'Customers', 'Products', 'Bidding'],
            'description' => 'Regional Heads, Account Executives, and Tender Estimation Engineers.',
        ],
        'Service Field' => [
            'key' => 'Service Field',
            'name' => 'Service Field Engineering',
            'screens' => ['Dashboard', 'Products', 'Customers'],
            'description' => 'On-site installation, maintenance, battery health, and emergency breakdown visits.',
        ],
        'Service Desk' => [
            'key' => 'Service Desk',
            'name' => 'Service Desk & Helpdesk',
            'screens' => ['Dashboard', 'Customers', 'Products', 'Orders'],
            'description' => 'Central call intake, regional technician assignment, AMC ticket dispatch.',
        ],
        'Repair' => [
            'key' => 'Repair',
            'name' => 'Repair & Component Salvage',
            'screens' => ['Dashboard', 'Products', 'Scrap'],
            'description' => 'In-house workshop repair, PCB rework, RMA diagnosis, testing benches.',
        ],
        'Operations/Warehouse' => [
            'key' => 'Operations/Warehouse',
            'name' => 'Operations & Warehouse Logistics',
            'screens' => ['Dashboard', 'Products', 'Orders', 'Scrap'],
            'description' => 'Hub inventory receipt, dispatch, bin tracking, and scrap discharge logging.',
        ],
        'Project' => [
            'key' => 'Project',
            'name' => 'Project & Solar Engineering',
            'screens' => ['Dashboard', 'Products', 'Orders', 'Customers'],
            'description' => 'Turnkey solar installations, site surveys, commissioning sign-offs.',
        ],
        'Admin' => [
            'key' => 'Admin',
            'name' => 'System Administration & IT',
            'screens' => ['Dashboard', 'Orders', 'Invoices', 'Products', 'Customers', 'Bidding', 'Scrap', 'AuditLogs', 'Identity'],
            'description' => 'Zero-code role matrix administration, audit log oversight, security policy guard.',
        ],
        'Customer' => [
            'key' => 'Customer',
            'name' => 'Customer Portal Profile',
            'screens' => ['Dashboard', 'Orders'],
            'description' => 'Read-only progress status tracking, AMC asset view; strictly isolated from cost data.',
        ],
    ];

    /**
     * 14 Named Organizational Archetype Templates (SS-35)
     */
    public const ARCHETYPES = [
        [
            'title' => 'Managing Director / Executive Owner',
            'role_type' => 'Executive',
            'scope' => 'global',
            'level' => 'L3',
            'department' => 'Executive Board',
            'sample_name' => 'Alexander Vance',
            'email' => 'admin@avoerp.com',
        ],
        [
            'title' => 'Chief Financial Officer (CFO)',
            'role_type' => 'Finance',
            'scope' => 'global',
            'level' => 'L3',
            'department' => 'Finance & Accounting',
            'sample_name' => 'Arthur Wellesley',
            'email' => 'cfo@avoerp.com',
        ],
        [
            'title' => 'Finance / Billing Executive',
            'role_type' => 'Finance',
            'scope' => 'branch',
            'level' => 'L1',
            'department' => 'Finance & Accounting',
            'sample_name' => 'Cassandra Cole',
            'email' => 'accountant@avoerp.com',
        ],
        [
            'title' => 'Regional Sales Head',
            'role_type' => 'Sales',
            'scope' => 'region',
            'level' => 'L2',
            'department' => 'Commercial Sales',
            'sample_name' => 'Morgan Blake',
            'email' => 'manager@avoerp.com',
        ],
        [
            'title' => 'Sales Executive (Tender Bidding)',
            'role_type' => 'Sales',
            'scope' => 'own',
            'level' => 'L1',
            'department' => 'Commercial Sales',
            'sample_name' => 'Lucas Reed (Salesperson A)',
            'email' => 'sales@avoerp.com',
        ],
        [
            'title' => 'Sales Executive (Competing Integrator)',
            'role_type' => 'Sales',
            'scope' => 'own',
            'level' => 'L1',
            'department' => 'Commercial Sales',
            'sample_name' => 'Siddharth Roy (Salesperson B)',
            'email' => 'sales2@avoerp.com',
        ],
        [
            'title' => 'Field Service Engineer',
            'role_type' => 'Service Field',
            'scope' => 'own',
            'level' => 'L1',
            'department' => 'Field Engineering',
            'sample_name' => 'Vikram Seth',
            'email' => 'field@avoerp.com',
        ],
        [
            'title' => 'Regional Service Coordinator',
            'role_type' => 'Service Desk',
            'scope' => 'region',
            'level' => 'L2',
            'department' => 'Service Desk',
            'sample_name' => 'Priya Nair',
            'email' => 'coordinator@avoerp.com',
        ],
        [
            'title' => 'Central Helpdesk Agent',
            'role_type' => 'Service Desk',
            'scope' => 'branch',
            'level' => 'L1',
            'department' => 'Service Desk',
            'sample_name' => 'Rahul Verma',
            'email' => 'helpdesk@avoerp.com',
        ],
        [
            'title' => 'Repair Center Technician',
            'role_type' => 'Repair',
            'scope' => 'branch',
            'level' => 'L1',
            'department' => 'Repair Workshop',
            'sample_name' => 'Anand Shrestha',
            'email' => 'repair@avoerp.com',
        ],
        [
            'title' => 'Repair Workshop Lead',
            'role_type' => 'Repair',
            'scope' => 'branch',
            'level' => 'L2',
            'department' => 'Repair Workshop',
            'sample_name' => 'Deepak Chopra',
            'email' => 'repairlead@avoerp.com',
        ],
        [
            'title' => 'Warehouse Operator (Blind Scrap)',
            'role_type' => 'Operations/Warehouse',
            'scope' => 'branch',
            'level' => 'L1',
            'department' => 'Warehouse Logistics',
            'sample_name' => 'Harper Quinn (Operator)',
            'email' => 'clerk@avoerp.com',
        ],
        [
            'title' => 'Operations / Warehouse Hub Manager',
            'role_type' => 'Operations/Warehouse',
            'scope' => 'region',
            'level' => 'L2',
            'department' => 'Warehouse Logistics',
            'sample_name' => 'Sanjay Mehta',
            'email' => 'ops@avoerp.com',
        ],
        [
            'title' => 'Solar Project Engineer',
            'role_type' => 'Project',
            'scope' => 'region',
            'level' => 'L2',
            'department' => 'Solar Projects',
            'sample_name' => 'Rohan Sen',
            'email' => 'project@avoerp.com',
        ],
    ];

    public static function getAllUsers(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT id, name, email, role, role_type, scope, level, designation, region, branch, department, reporting_manager_id, created_at FROM users ORDER BY id ASC");
        $users = $stmt->fetchAll();

        // Attach reporting manager names
        $userMap = [];
        foreach ($users as $u) {
            $userMap[$u['id']] = $u['name'];
        }

        foreach ($users as &$u) {
            $mgrId = $u['reporting_manager_id'];
            $u['reporting_manager_name'] = $mgrId && isset($userMap[$mgrId]) ? $userMap[$mgrId] : null;
        }

        return $users;
    }

    public static function createUser(array $data, array $actor): array
    {
        if (empty($data['email']) || empty($data['name'])) {
            throw new InvalidArgumentException("User Name and Email are mandatory");
        }

        $email = trim($data['email']);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Invalid user email address format: '{$email}'.");
        }

        $pdo = Database::getConnection();
        $check = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
        $check->execute(['email' => $data['email']]);
        if ($check->fetch()) {
            throw new InvalidArgumentException("A user with email '{$data['email']}' already exists");
        }

        $roleType = $data['role_type'] ?? 'Sales';
        $scope = $data['scope'] ?? 'own';
        $level = $data['level'] ?? 'L1';
        $legacyRole = self::deriveLegacyRole($roleType);

        $insertData = [
            'name' => trim($data['name']),
            'email' => trim($data['email']),
            'password_hash' => password_hash($data['password'] ?? 'avo123', PASSWORD_BCRYPT),
            'role' => $legacyRole,
            'role_type' => $roleType,
            'scope' => $scope,
            'level' => $level,
            'designation' => $data['designation'] ?? "{$roleType} {$level}",
            'region' => $data['region'] ?? 'North',
            'branch' => $data['branch'] ?? 'HQ Hub',
            'department' => $data['department'] ?? 'Operations',
            'reporting_manager_id' => !empty($data['reporting_manager_id']) ? (int) $data['reporting_manager_id'] : null,
        ];

        $id = User::insert($insertData);

        AuditLog::log(
            $actor['id'],
            $actor['name'],
            $actor['role'],
            'PROVISION_USER',
            'User',
            (string) $id,
            null,
            $insertData
        );

        return User::find($id);
    }

    public static function updateUser(int $id, array $data, array $actor): array
    {
        $existing = User::find($id);
        if (!$existing) {
            throw new InvalidArgumentException("User not found");
        }

        $fields = ['name', 'role_type', 'scope', 'level', 'designation', 'region', 'branch', 'department', 'reporting_manager_id'];
        $updateData = [];

        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $updateData[$field] = $data[$field];
            }
        }

        if (isset($updateData['role_type'])) {
            $updateData['role'] = self::deriveLegacyRole($updateData['role_type']);
        }

        if (!empty($data['password'])) {
            $updateData['password_hash'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }

        User::update($id, $updateData);

        AuditLog::log(
            $actor['id'],
            $actor['name'],
            $actor['role'],
            'UPDATE_USER_PERMISSIONS',
            'User',
            (string) $id,
            $existing,
            $updateData
        );

        return User::find($id);
    }

    public static function deleteUser(int $id, array $actor): bool
    {
        $existing = User::find($id);
        if (!$existing) {
            throw new InvalidArgumentException("User not found");
        }

        // Prevent self-deletion
        if ((int)$actor['id'] === $id) {
            throw new InvalidArgumentException("You cannot delete your own active account.");
        }

        // Prevent deleting the root administrator (id: 1)
        if ($id === 1) {
            throw new InvalidArgumentException("The primary enterprise administrator account cannot be deleted.");
        }

        $pdo = Database::getConnection();

        // Nullify reporting manager references for any subordinate users
        $nullStmt = $pdo->prepare("UPDATE users SET reporting_manager_id = NULL WHERE reporting_manager_id = ?");
        $nullStmt->execute([$id]);

        User::delete($id);

        AuditLog::log(
            $actor['id'],
            $actor['name'],
            $actor['role'],
            'DELETE_USER',
            'User',
            (string) $id,
            $existing,
            null
        );

        return true;
    }

    public static function getPermissionMatrix(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT * FROM permission_matrix ORDER BY id ASC");
        $records = $stmt->fetchAll();

        if (empty($records)) {
            self::seedDefaultPermissionMatrix($pdo);
            $stmt = $pdo->query("SELECT * FROM permission_matrix ORDER BY id ASC");
            $records = $stmt->fetchAll();
        }

        return $records;
    }

    public static function updatePermissionMatrixRow(int $id, array $data, array $actor): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE permission_matrix SET can_view = :v, can_create = :c, can_edit = :e, can_approve = :a, can_delete = :d WHERE id = :id");
        $res = $stmt->execute([
            'v' => $data['can_view'] ? 1 : 0,
            'c' => $data['can_create'] ? 1 : 0,
            'e' => $data['can_edit'] ? 1 : 0,
            'a' => $data['can_approve'] ? 1 : 0,
            'd' => $data['can_delete'] ? 1 : 0,
            'id' => $id,
        ]);

        AuditLog::log(
            $actor['id'],
            $actor['name'],
            $actor['role'],
            'UPDATE_PERMISSION_MATRIX',
            'PermissionMatrix',
            (string) $id,
            null,
            $data
        );

        return $res;
    }

    public static function bulkUpdateRole(string $roleType, string $preset, array $actor): bool
    {
        $pdo = Database::getConnection();
        [$v, $c, $e, $a, $d] = match ($preset) {
            'all' => [1, 1, 1, 1, 1],
            'read_only' => [1, 0, 0, 0, 0],
            'standard' => [1, 1, 1, 0, 0],
            'none' => [0, 0, 0, 0, 0],
            default => [1, 0, 0, 0, 0],
        };

        $stmt = $pdo->prepare("UPDATE permission_matrix SET can_view = :v, can_create = :c, can_edit = :e, can_approve = :a, can_delete = :d WHERE role_type = :rt");
        $res = $stmt->execute([
            'v' => $v,
            'c' => $c,
            'e' => $e,
            'a' => $a,
            'd' => $d,
            'rt' => $roleType,
        ]);

        AuditLog::log(
            $actor['id'],
            $actor['name'],
            $actor['role'],
            'BULK_UPDATE_ROLE_PERMISSIONS',
            'PermissionMatrix',
            $roleType,
            null,
            ['preset' => $preset, 'view' => $v, 'create' => $c, 'edit' => $e, 'approve' => $a, 'delete' => $d]
        );

        return $res;
    }

    public static function seedDefaultPermissionMatrix(PDO $pdo): void
    {
        $existingCount = (int) $pdo->query("SELECT COUNT(*) FROM permission_matrix")->fetchColumn();
        if ($existingCount >= 90) {
            return; // Already populated, avoid duplicating rows
        }

        $modules = [
            ['key' => 'dashboard', 'name' => 'Executive Dashboard'],
            ['key' => 'orders', 'name' => 'Sales Orders Pipeline'],
            ['key' => 'invoices', 'name' => 'Authoritative Invoices & Billing'],
            ['key' => 'products', 'name' => 'Inventory & Products'],
            ['key' => 'customers', 'name' => 'Customers & Credit Limits'],
            ['key' => 'bidding', 'name' => 'Chinese-Wall RFP Bidding'],
            ['key' => 'scrap', 'name' => 'Warehouse Blind Scrap Ledger'],
            ['key' => 'audit_logs', 'name' => 'Immutable Audit Trail'],
            ['key' => 'identity', 'name' => 'Identity & Role Matrix (SS6)'],
        ];

        foreach (array_keys(self::ROLE_TYPES) as $rt) {
            foreach ($modules as $m) {
                $canView = in_array($m['name'], self::ROLE_TYPES[$rt]['screens']) || in_array($m['key'], self::ROLE_TYPES[$rt]['screens']) ? 1 : 0;
                $canCreate = in_array($rt, ['Admin', 'Executive']) || ($rt === 'Sales' && in_array($m['key'], ['orders', 'customers', 'bidding'])) || ($rt === 'Operations/Warehouse' && in_array($m['key'], ['scrap', 'products'])) ? 1 : 0;
                $canApprove = in_array($rt, ['Admin', 'Executive']) ? 1 : 0;
                $canDelete = $rt === 'Admin' ? 1 : 0;

                $stmt = $pdo->prepare("INSERT INTO permission_matrix (role_type, min_level, module_key, module_name, can_view, can_create, can_edit, can_approve, can_delete) VALUES (:rt, 'L1', :mk, :mn, :cv, :cc, :ce, :ca, :cd)");
                $stmt->execute([
                    'rt' => $rt,
                    'mk' => $m['key'],
                    'mn' => $m['name'],
                    'cv' => $canView,
                    'cc' => $canCreate,
                    'ce' => $canCreate,
                    'ca' => $canApprove,
                    'cd' => $canDelete,
                ]);
            }
        }
    }

    private static function deriveLegacyRole(string $roleType): string
    {
        return match ($roleType) {
            'Admin' => 'admin',
            'Executive' => 'admin',
            'Finance' => 'accountant',
            'Sales' => 'sales_rep',
            'Operations/Warehouse' => 'inventory_clerk',
            default => 'sales_rep',
        };
    }
}

