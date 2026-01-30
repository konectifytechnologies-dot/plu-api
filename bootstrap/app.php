<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Foundation\Configuration\Middleware;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Illuminate\Http\Middleware\HandleCors;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        
    )
     ->withExceptions(function (Exceptions $exceptions) {

        $exceptions->renderable(function (
            ValidationException $e,
            $request
        ) {
            return response()->json([
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        });

    })
    ->withMiddleware(function (Middleware $middleware) {
        // CORS must run FIRST
        $middleware->append(HandleCors::class);
        $middleware->statefulApi();

        // Sanctum SPA middleware
        $middleware->append(EnsureFrontendRequestsAreStateful::class);
        $middleware->append(\App\Http\Middleware\CheckAuthMiddleware::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
