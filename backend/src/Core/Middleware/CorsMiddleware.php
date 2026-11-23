<?php

namespace App\Core\Middleware;

use App\Core\Request;
use App\Core\Response;

class CorsMiddleware
{
    public static function handle(Request $request): void
    {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
        header("Access-Control-Allow-Origin: {$origin}");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

        if ($request->getMethod() === 'OPTIONS') {
            http_response_code(Response::HTTP_OK);
            exit;
        }
    }
}

