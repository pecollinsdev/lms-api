<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: base_path('routes/api.php'),
        commands: base_path('routes/console.php'),
        health: '/up',
    )
    ->withProviders([
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        App\Providers\EventServiceProvider::class,
        App\Providers\RouteServiceProvider::class,
    ])
    ->withMiddleware(function (Middleware $middleware) {
        // Register global middleware here if needed
        // e.g. $middleware->register(App\Http\Middleware\JwtMiddleware::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Configure custom exception renderers here
    })
    ->create();