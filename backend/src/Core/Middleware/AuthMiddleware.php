<?php

namespace App\Core\Middleware;

use App\Core\Auth\JwtService;
use App\Core\Request;
use App\Core\Response;

class AuthMiddleware
{
    public static function handle(Request $request): void
    {
        $token = $request->getBearerToken();
        if (!$token) {
            Response::unauthorized('Authentication token is required');
        }

        $payload = JwtService::decode($token);
        if (!$payload) {
            Response::unauthorized('Invalid or expired authentication token');
        }

        $request->setUser($payload);
    }
}

