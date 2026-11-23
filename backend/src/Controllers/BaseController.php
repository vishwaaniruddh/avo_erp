<?php

namespace App\Controllers;

use App\Core\Response;

abstract class BaseController
{
    protected function ok(mixed $data = null, string $message = 'Successful request'): void
    {
        Response::ok($data, $message);
    }

    protected function created(mixed $data = null, string $message = 'Created successfully'): void
    {
        Response::created($data, $message);
    }

    protected function badRequest(string $message = 'Invalid request', mixed $errors = null): void
    {
        Response::badRequest($message, $errors);
    }

    protected function unauthorized(string $message = 'Unauthorized'): void
    {
        Response::unauthorized($message);
    }

    protected function forbidden(string $message = 'Permission denied'): void
    {
        Response::forbidden($message);
    }

    protected function notFound(string $message = 'Resource not found'): void
    {
        Response::notFound($message);
    }

    protected function unprocessable(string $message = 'Validation failed', mixed $errors = null): void
    {
        Response::unprocessable($message, $errors);
    }

    protected function serverError(string $message = 'Server error', mixed $details = null): void
    {
        Response::serverError($message, $details);
    }
}

