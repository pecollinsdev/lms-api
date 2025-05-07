<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * Global HTTP middleware stack.
     * These run on every request to your API.
     *
     * @var array
     */
    protected $middleware = [
        // Trust proxies (if behind a load-balancer)
        \Illuminate\Http\Middleware\TrustProxies::class,

        // Handle CORS requests
        \Illuminate\Http\Middleware\HandleCors::class,

        // Block during maintenance
        \Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance::class,

        // Validate post size
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,

        // Trim all request string inputs
        \Illuminate\Foundation\Http\Middleware\TrimStrings::class,

        // Convert empty strings to null
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,

        // Log all API requests
        \App\Http\Middleware\RequestLogger::class,
    ];

    /**
     * Route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'api' => [
            // Throttle requests (default: 60 requests/minute)
            'throttle:api',

            // Substitute route‐model bindings
            \Illuminate\Routing\Middleware\SubstituteBindings::class,

            // If you want **every** API request to run JWT validation automatically,
            // you can uncomment this line:
            \App\Http\Middleware\JwtMiddleware::class,
        ],
    ];

    /**
     * Route middleware.
     * You can assign these to specific routes or groups.
     *
     * @var array
     */
    protected $routeMiddleware = [
        // Laravel's default auth (session-based)
//      'auth'        => \App\Http\Middleware\Authenticate::class,

        // Our custom JWT guard
        'auth.jwt'    => \App\Http\Middleware\JwtMiddleware::class,

        // Bind route parameters to models
        'bindings'    => \Illuminate\Routing\Middleware\SubstituteBindings::class,

        // Rate-limiting
        'throttle'    => \Illuminate\Routing\Middleware\ThrottleRequests::class,
    ];
}
