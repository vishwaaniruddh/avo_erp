<?php

namespace App\Core;

class Response
{
    public const HTTP_OK = 200;
    public const HTTP_CREATED = 201;
    public const HTTP_BAD_REQUEST = 400;
    public const HTTP_UNAUTHORIZED = 401;
    public const HTTP_FORBIDDEN = 403;
    public const HTTP_NOT_FOUND = 404;
    public const HTTP_UNPROCESSABLE_ENTITY = 422;
    public const HTTP_INTERNAL_SERVER_ERROR = 500;

    public static function json(mixed $data, int $status = self::HTTP_OK, array $headers = []): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');

        foreach ($headers as $key => $val) {
            header("{$key}: {$val}");
        }

        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function ok(mixed $data = null, string $message = 'Successful request'): void
    {
        self::json([
            'success' => true,
            'status' => self::HTTP_OK,
            'message' => $message,
            'data' => $data,
        ], self::HTTP_OK);
    }

    public static function created(mixed $data = null, string $message = 'Created successfully'): void
    {
        self::json([
            'success' => true,
            'status' => self::HTTP_CREATED,
            'message' => $message,
            'data' => $data,
        ], self::HTTP_CREATED);
    }

    public static function badRequest(string $message = 'Invalid request', mixed $errors = null): void
    {
        self::json([
            'success' => false,
            'status' => self::HTTP_BAD_REQUEST,
            'message' => $message,
            'errors' => $errors,
        ], self::HTTP_BAD_REQUEST);
    }

    public static function unauthorized(string $message = 'Unauthorized'): void
    {
        self::json([
            'success' => false,
            'status' => self::HTTP_UNAUTHORIZED,
            'message' => $message,
        ], self::HTTP_UNAUTHORIZED);
    }

    public static function forbidden(string $message = 'Permission denied (RBAC enforced)'): void
    {
        self::json([
            'success' => false,
            'status' => self::HTTP_FORBIDDEN,
            'message' => $message,
        ], self::HTTP_FORBIDDEN);
    }

    public static function notFound(string $message = 'Resource not found'): void
    {
        self::json([
            'success' => false,
            'status' => self::HTTP_NOT_FOUND,
            'message' => $message,
        ], self::HTTP_NOT_FOUND);
    }

    public static function unprocessable(string $message = 'Validation failed', mixed $errors = null): void
    {
        self::json([
            'success' => false,
            'status' => self::HTTP_UNPROCESSABLE_ENTITY,
            'message' => $message,
            'errors' => $errors,
        ], self::HTTP_UNPROCESSABLE_ENTITY);
    }

    public static function serverError(string $message = 'Server error', mixed $details = null): void
    {
        self::json([
            'success' => false,
            'status' => self::HTTP_INTERNAL_SERVER_ERROR,
            'message' => $message,
            'details' => $details,
        ], self::HTTP_INTERNAL_SERVER_ERROR);
    }
}

