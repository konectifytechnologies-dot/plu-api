<?php

namespace App\Traits;


use Illuminate\Http\Response as HttpResponse;
use Symfony\Component\HttpFoundation\Response;

trait ApiResponse
{
    protected function success(mixed $data = null,string $message = 'Success',int $code = Response::HTTP_OK): HttpResponse 
    {
        return response([
            'success' => true,
            'code'=>0,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    protected function created(mixed $data = null, string $message = 'Resource created successfully'): HttpResponse 
    {
        return $this->success($data, $message, Response::HTTP_CREATED);
    }


    protected function error(string $message = 'Error',int $code = Response::HTTP_BAD_REQUEST,array $errors = []): HttpResponse 
    {
        $response = [
            'success' => false,
            'code'=>3,
            'message' => $message,
        ];

        if (! empty($errors)) {
            $response['errors'] = $errors;
        }

        return response($response, $code);
    }

    protected function notFound(string $message = 'Resource not found'): HttpResponse
    {
        return $this->error($message, Response::HTTP_NOT_FOUND);
    }

    protected function unauthorized(string $message = 'Unauthorized'): HttpResponse
    {
        return $this->error($message, Response::HTTP_UNAUTHORIZED);
    }

    protected function forbidden(string $message = 'Forbidden'): HttpResponse
    {
        return $this->error($message, Response::HTTP_FORBIDDEN);
    }

    protected function validationError(array $errors, string $message = 'Validation failed'): HttpResponse
    {
        return $this->error($message, Response::HTTP_UNPROCESSABLE_ENTITY, $errors);
    }
}
