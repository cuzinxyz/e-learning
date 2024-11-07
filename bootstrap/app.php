<?php

declare(strict_types=1);

use App\Constants\Messages;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {})
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (NotFoundHttpException $e, Request $req) {
            if ($req->is('api/*')) {
                Log::channel('exception')->error("Exception: {$e->getMessage()}");

                return response()->json([
                    'status' => false,
                    'message' => Messages::ROUTE_NOT_FOUND,
                    'data' => null,
                ], 404);
            }
        });

        $exceptions->render(function (HttpException $e, Request $req) {
            if ($req->is('api/*')) {
                Log::channel('exception')->error("Exception: {$e->getMessage()}");

                return response()->json([
                    'status' => false,
                    'message' => Messages::NOT_SUPPORT,
                    'data' => null,
                ], 405);
            }
        });
    })->create();
