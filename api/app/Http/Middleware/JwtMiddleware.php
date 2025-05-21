<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;

class JwtMiddleware
{
    public function handle($request, Closure $next)
    {
        try {
            // First try to get token from cookie
            $token = $request->cookie('token');
            
            // If not in cookie, try Authorization header
            if (!$token) {
                $header = $request->header('Authorization', '');
                if (preg_match('/Bearer\s(\S+)/', $header, $matches)) {
                    $token = $matches[1];
                }
            }

            if (!$token) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Token not provided'
                ], Response::HTTP_UNAUTHORIZED);
            }

            // Check token and authenticate user
            if (!$user = JWTAuth::setToken($token)->authenticate()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found'
                ], Response::HTTP_UNAUTHORIZED);
            }
        } catch (TokenExpiredException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token has expired'
            ], Response::HTTP_UNAUTHORIZED);
        } catch (TokenInvalidException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token is invalid'
            ], Response::HTTP_UNAUTHORIZED);
        } catch (JWTException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token not provided'
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
