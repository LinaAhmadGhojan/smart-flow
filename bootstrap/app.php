<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->booting(function () {
        // Vite dev "hot" file must never ship to production — it makes @vite load
        // http://127.0.0.1:5173/resources/css/app.css (ERR_CONNECTION_REFUSED).
        if (! app()->environment('production')) {
            return;
        }

        foreach ([public_path('hot'), base_path('hot')] as $hotFile) {
            if (is_file($hotFile)) {
                @unlink($hotFile);
            }
        }
    })
    ->withMiddleware(function (Middleware $middleware) {
        // SPA admin uses Bearer tokens; API must return 401 JSON, not redirect to login.
        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return null;
            }

            return '/admin';
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
