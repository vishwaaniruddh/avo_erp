<?php

namespace App\Core\Middleware;

use App\Core\Auth\CasbinEnforcer;
use App\Core\Request;
use App\Core\Response;

class CasbinMiddleware
{
    public static function handle(Request $request): void
    {
        $user = $request->getUser();
        if (!$user) {
            Response::unauthorized('Authentication required for RBAC check');
        }

        $path = $request->getPath();
        $method = $request->getMethod();

        $allowed = CasbinEnforcer::enforceUser($user, $path, $method);
        if (!$allowed) {
            $roleDesc = "{$user['role_type']} (Scope: {$user['scope']}, Level: {$user['level']})";
            Response::forbidden("Permission denied (Zero-Trust API Enforced): Role '{$roleDesc}' is not authorized to {$method} on {$path}");
        }
    }
}
