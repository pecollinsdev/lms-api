<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use App\Services\JwtService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Merge custom JWT config if present
        $jwtConfigPath = config_path('jwt.php');
        if (file_exists($jwtConfigPath)) {
            $this->mergeConfigFrom($jwtConfigPath, 'jwt');
        }

        // Register the JWT service as a singleton
        $this->app->singleton(JwtService::class, function ($app) {
            return new JwtService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force HTTPS scheme in production
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Use Bootstrap for pagination
        Paginator::useBootstrap();
    }
}
