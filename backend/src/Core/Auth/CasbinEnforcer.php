<?php

namespace App\Core\Auth;

use Casbin\Enforcer;

class CasbinEnforcer
{
    private static ?Enforcer $instance = null;

    public static function getInstance(): Enforcer
    {
        if (self::$instance === null) {
            $baseDir = dirname(__DIR__, 3);
            $modelPath = $baseDir . '/config/rbac_model.conf';
            $policyPath = $baseDir . '/config/rbac_policy.csv';

            self::$instance = new Enforcer($modelPath, $policyPath);
        }

        return self::$instance;
    }

    public static function setInstance(?Enforcer $enforcer): void
    {
        self::$instance = $enforcer;
    }

    /**
     * Enforce Decoupled 3D User Model:
     * Access = Role Type + Scope + Level (SS-23, SS-33, SS-24)
     */
    public static function enforceUser(array $user, string $path, string $method): bool
    {
        $role = $user['role'] ?? 'sales_rep';
        $roleType = $user['role_type'] ?? 'Sales';
        $level = $user['level'] ?? 'L1';
        $scope = $user['scope'] ?? 'own';
        $method = strtoupper($method);

        // System Admin or Executive L3 has full authorization
        if ($role === 'admin' || ($roleType === 'Executive' && $level === 'L3') || $roleType === 'Admin') {
            return true;
        }

        // =========================================================================
        // SPECIAL SECURITY GATE: Warehouse Scrap Blind-Entry & Anti-Collusion (RA-07, RA-08)
        // =========================================================================
        if (preg_match('#^/api/scrap#', $path)) {
            // Write / Submit physical scrap discharge
            if ($method === 'POST') {
                // Warehouse operators, Operations Manager, and Executive can submit
                return in_array($roleType, ['Operations/Warehouse', 'Executive', 'Admin', 'Finance']);
            }

            // Read / View past scrap ledger
            if ($method === 'GET') {
                // STRICT BLIND ENTRY GATE (RA-07):
                // Warehouse Operators (Operations/Warehouse at L1) are FORBIDDEN from viewing past scrap records!
                if ($roleType === 'Operations/Warehouse' && $level === 'L1') {
                    return false; // Returns HTTP 403 Forbidden
                }

                // RA-08: Scrap ledger visible ONLY to Owner / CFO / COO (Executive, L3 Finance, Admin)
                return in_array($roleType, ['Executive', 'Admin']) || ($roleType === 'Finance' && in_array($level, ['L2', 'L3']));
            }
        }

        // =========================================================================
        // SPECIAL SECURITY GATE: Chinese-Wall Bidding & RFP (RA-04, RA-05)
        // =========================================================================
        if (preg_match('#^/api/rfp-bids#', $path)) {
            if ($method === 'POST') {
                return in_array($roleType, ['Sales', 'Executive', 'Admin']);
            }
            if ($method === 'GET') {
                return in_array($roleType, ['Sales', 'Executive', 'Admin', 'Finance']);
            }
        }

        // =========================================================================
        // SPECIAL SECURITY GATE: User & Identity Module Access (RA-02, SS-25)
        // Authenticated users can browse the directory; creation & editing requires Admin/Executive/L2+
        // =========================================================================
        if (preg_match('#^/api/identity#', $path) || preg_match('#^/api/users#', $path)) {
            if ($method === 'GET') {
                return true;
            }
            return $role === 'admin' || in_array($roleType, ['Admin', 'Executive']) || in_array($level, ['L2', 'L3']);
        }

        // =========================================================================
        // SPECIAL SECURITY GATE: Superadmin Masters & Email Settings
        // =========================================================================
        if (preg_match('#^/api/email#', $path) || preg_match('#^/api/masters#', $path)) {
            if ($method === 'GET') {
                return true;
            }
            return $role === 'admin' || in_array($roleType, ['Admin', 'Executive']) || in_array($level, ['L2', 'L3']);
        }

        // =========================================================================
        // SPECIAL SECURITY GATE: Sales, Marketing, Tender & Bid Platform (06/09)
        // =========================================================================
        if (preg_match('#^/api/sales#', $path)) {
            // Below-MSP approval gate: requires VP / Director (L3) or Executive / Admin
            if (preg_match('#^/api/sales/quotes/\d+/approve#', $path)) {
                return in_array($roleType, ['Executive', 'Admin']) || $level === 'L3';
            }
            // General Sales access: Sales, Executive, Admin, Finance, Project
            return in_array($roleType, ['Sales', 'Executive', 'Admin', 'Finance', 'Project']);
        }

        // Standard role enforcement fallback via Casbin policy engine
        $mappedRole = self::mapRoleTypeToLegacy($roleType, $role);
        return self::getInstance()->enforce($mappedRole, $path, $method);
    }

    public static function enforce(string $role, string $path, string $method): bool
    {
        if ($role === 'admin') {
            return true;
        }

        return self::getInstance()->enforce($role, $path, strtoupper($method));
    }

    private static function mapRoleTypeToLegacy(string $roleType, string $fallback): string
    {
        return match ($roleType) {
            'Admin' => 'admin',
            'Executive' => 'admin',
            'Finance' => 'accountant',
            'Sales' => 'sales_rep',
            'Operations/Warehouse' => 'inventory_clerk',
            default => $fallback,
        };
    }
}
