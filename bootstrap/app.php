<?php

use App\Http\Middleware\LogAfterRequestMiddleware;
use App\Http\Middleware\SetContextMiddleware;
use App\Models\ErrorLog;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'auth:sanctum',
            'role' => \App\Http\Middleware\Role::class,
            'permission' => \App\Http\Middleware\Permission::class,
        ]);
        $middleware->append(SetContextMiddleware::class);
        $middleware->append(LogAfterRequestMiddleware::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->reportable(function (Exception $e) {
            ErrorLog::storeExceptionError($e);
        });
        $exceptions->render(function (Exception $e, Request $request) {
            if ($request->is('api/*')) {
               
                return response(ErrorLog::getErrorResponse($e), $e?->status ?? 400);
            }
        });
    })->create();
