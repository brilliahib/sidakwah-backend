<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'auth' => \Tymon\JWTAuth\Http\Middleware\Authenticate::class,
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // GLOBAL validation error
        $exceptions->render(function (ValidationException $e, $request) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'statusCode' => 422,
                    'message' => 'Validation failed',
                ],
                'data' => $e->errors(),
            ], 422);
        });

        // GLOBAL unauthenticated (JWT / auth)
        $exceptions->render(function (AuthenticationException|UnauthorizedHttpException $e) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'statusCode' => 401,
                    'message' => 'Unauthenticated',
                ],
                'data' => null,
            ], 401);
        });
    })->create();
