<?php

declare(strict_types=1);

use App\Constants\Messages;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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
                ], Response::HTTP_NOT_FOUND);
            }
        });

        $exceptions->render(function (HttpException $e, Request $req) {
            if ($req->is('api/*')) {
                Log::channel('exception')->error("Exception: {$e->getMessage()}");

                return response()->json([
                    'status' => false,
                    'message' => Messages::NOT_SUPPORT,
                    'data' => null,
                ], Response::HTTP_METHOD_NOT_ALLOWED);
            }
        });

        $exceptions->render(function(AuthenticationException $e, Request $req) {
            if ($req->is('api/*')) {
                Log::channel('exception')->error("Exception: {$e->getMessage()}");

                return response()->json([
                    'status' => false,
                    'message' => Messages::NOT_AUTHENTICATED,
                    'data' => null
                ], Response::HTTP_UNAUTHORIZED);
            }
        });
    })->create();
