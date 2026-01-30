<?php

namespace App\Services;

use App\Exceptions\ApiException;

class ErrorService
{
    public function notFound(string $message = 'Not Found'): never
    {
        throw new ApiException($message, 1001, 404);
    }

    public function badRequest(string $message, int $code = 3): never
    {
        throw new ApiException($message, $code, 400);
    }

    public function forbidden(string $message = 'Forbidden'): never
    {
        throw new ApiException($message, 1003, 403);
    }

    public function unauthorized(string $message = 'Unauthorized'): never
    {
        throw new ApiException($message, 1002, 401);
    }
}
