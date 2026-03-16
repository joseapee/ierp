<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'tenant' => \App\Http\Middleware\ResolveTenant::class,
            'permission' => \App\Http\Middleware\CheckPermission::class,
            'super.admin' => \App\Http\Middleware\SuperAdminOnly::class,
            'subscription' => \App\Http\Middleware\CheckSubscription::class,
            'feature' => \App\Http\Middleware\CheckFeature::class,
            'setup.complete' => \App\Http\Middleware\EnsureSetupComplete::class,
        ]);

        $middleware->appendToGroup('web', \App\Http\Middleware\ResolveTenant::class);

        $middleware->validateCsrfTokens(except: [
            'webhook/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
