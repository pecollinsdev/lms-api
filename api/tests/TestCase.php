<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Contracts\Console\Kernel;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        // Register the JWT middleware
        $app['router']->aliasMiddleware('auth.jwt', \App\Http\Middleware\JwtMiddleware::class);

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();
        
        // Set the base URL for all requests
        $this->withServerVariables([
            'HTTP_HOST' => 'localhost',
            'REQUEST_URI' => '/lms-api/api'
        ]);
    }
}
