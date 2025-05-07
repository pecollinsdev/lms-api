<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RequestLogger
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $startTime = microtime(true);

        try {
            $response = $next($request);
            $duration = microtime(true) - $startTime;
            $this->logRequest($request, $response, $duration);
            return $response;
        } catch (\Exception $e) {
            $duration = microtime(true) - $startTime;
            $this->logError($request, $e, $duration);
            throw $e;
        }
    }

    /**
     * Log the request details
     *
     * @param Request $request
     * @param Response $response
     * @param float $duration
     * @return void
     */
    private function logRequest(Request $request, Response $response, float $duration): void
    {
        $logData = [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'headers' => $request->headers->all(),
            'body' => $request->all(),
            'status' => $response->getStatusCode(),
            'duration' => round($duration * 1000, 2) . 'ms',
            'user_agent' => $request->userAgent(),
            'cookies' => $request->cookies->all(),
        ];

        // Remove sensitive data from headers
        if (isset($logData['headers']['authorization'])) {
            $logData['headers']['authorization'] = '[REDACTED]';
        }

        Log::info('API Request', $logData);
    }

    /**
     * Log error details
     *
     * @param Request $request
     * @param \Exception $e
     * @param float $duration
     * @return void
     */
    private function logError(Request $request, \Exception $e, float $duration): void
    {
        $logData = [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'headers' => $request->headers->all(),
            'body' => $request->all(),
            'duration' => round($duration * 1000, 2) . 'ms',
            'user_agent' => $request->userAgent(),
            'cookies' => $request->cookies->all(),
            'error' => [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]
        ];

        // Remove sensitive data from headers
        if (isset($logData['headers']['authorization'])) {
            $logData['headers']['authorization'] = '[REDACTED]';
        }

        Log::error('API Request Error', $logData);
    }
} 