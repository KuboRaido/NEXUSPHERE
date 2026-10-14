<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;
// use App\Http\Middleware\HandleInertiaRequests; // ← Inertia未使用ならコメントアウト

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        using: function () {
            // API（v1）
            Route::middleware('api')
                ->prefix('api')
                ->group(function () {
                    Route::group([], base_path('routes/api.php'));
                });

            // 画面（Blade等）
            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        },
        commands: __DIR__ . '/../routes/console.php',
        health: '/healthz',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Inertia使うときだけ：
        // $middleware->web(append: [ HandleInertiaRequests::class ]);

        // SPA + Sanctum（Cookie認証）を使うなら推奨
        $middleware->appendToGroup('api', EnsureFrontendRequestsAreStateful::class);

        $middleware->alias([
            'auth'     => \App\Http\Middleware\Authenticate::class,
            'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
    $exceptions->render(function (PostTooLargeException $e, Request $request) {
        return redirect()->back()
            ->withInput()
            ->withErrors(['file_error' => 'ファイルサイズが大きすぎます。']);
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->renderable(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
        });
    })->create();
