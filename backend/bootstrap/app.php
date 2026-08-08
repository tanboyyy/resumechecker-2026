<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->statefulApi();
        $middleware->api(prepend: [
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
        ]);
        // Stripe posts here server-to-server with no session and no CSRF token.
        // Authenticity is established by the signature check in the controller.
        $middleware->validateCsrfTokens(except: [
            'api/billing/webhook',
        ]);

        // This is an API with no server-rendered login page. Without this, a
        // guest request that does not explicitly ask for JSON (a browser tab,
        // an <iframe> loading a PDF) tries to redirect to a route('login')
        // that does not exist and 500s instead of returning 401.
        $middleware->redirectGuestsTo(fn (Request $request) => $request->is('api/*') ? null : '/');

        $middleware->alias([
            'plan' => \App\Http\Middleware\EnsurePlanAllows::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
