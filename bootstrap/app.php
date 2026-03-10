<?php

use App\Facades\ApiResponse;
use App\Services\ApiResponse\StatusCode;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        channels: __DIR__ . '/../routes/channels.php',
        health: '/up',
        then: function () {
            // User API Routes - Version 1
            Route::middleware(['api'])
                ->prefix('api/v1/users')
                ->name('user.v1.')
                ->group(base_path('routes/api.php'));

            // Admin API Routes - Version 1
            Route::middleware(['api'])
                ->prefix('api/v1/admins')
                ->name('admin.v1.')
                ->group(base_path('routes/admin.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        // ── Only intercept JSON / API requests ────────────────────────────
        $exceptions->render(function (\Throwable $e, Request $request) {

            if (! $request->expectsJson()) {
                return null; // Fall through to default HTML handler
            }

            // ── Validation (422) ──────────────────────────────────────────
            if ($e instanceof ValidationException) {
                return ApiResponse::respondWithError(
                    message   : 'The given data was invalid.',
                    statusCode: StatusCode::VALIDATION_ERROR,
                    httpStatus: 422,
                    errors    : $e->errors(),
                )->send();
            }

            // ── Unauthenticated (401) ─────────────────────────────────────
            if ($e instanceof AuthenticationException) {
                return ApiResponse::respondWithError(
                    message   : $e->getMessage() ?: 'Unauthenticated.',
                    statusCode: StatusCode::UNAUTHORIZED,
                    httpStatus: 401,
                )->send();
            }

            // ── Authorisation (403) ───────────────────────────────────────
            if ($e instanceof AuthorizationException) {
                return ApiResponse::respondWithError(
                    message   : $e->getMessage() ?: 'This action is unauthorized.',
                    statusCode: StatusCode::FORBIDDEN,
                    httpStatus: 403,
                )->send();
            }

            // ── Model not found → 404 ─────────────────────────────────────
            if ($e instanceof ModelNotFoundException) {
                $model   = class_basename($e->getModel());
                return ApiResponse::respondWithError(
                    message   : "{$model} not found.",
                    statusCode: StatusCode::NOT_FOUND,
                    httpStatus: 404,
                )->send();
            }

            // ── Generic HTTP exceptions (404, 405 …) ──────────────────────
            if ($e instanceof NotFoundHttpException) {
                return ApiResponse::respondWithError(
                    message   : 'The requested resource was not found.',
                    statusCode: StatusCode::NOT_FOUND,
                    httpStatus: 404,
                )->send();
            }

            if ($e instanceof HttpException) {
                return ApiResponse::respondWithError(
                    message   : $e->getMessage() ?: 'HTTP error.',
                    statusCode: StatusCode::SERVER_ERROR,
                    httpStatus: $e->getStatusCode(),
                )->send();
            }

            // ── Catch-all – 500 ───────────────────────────────────────────
            return ApiResponse::respondWithError(
                message   : app()->hasDebugModeEnabled()
                    ? $e->getMessage()
                    : 'An unexpected error occurred. Please try again later.',
                statusCode: StatusCode::SERVER_ERROR,
                httpStatus: 500,
            )->send();
        });

    })
    ->create();

