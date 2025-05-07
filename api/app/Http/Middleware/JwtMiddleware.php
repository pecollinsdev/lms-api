<?php

namespace App\Http\Middleware;

use Closure;
use App\Services\JwtService;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class JwtMiddleware
{
    protected JwtService $jwt;

    public function __construct(JwtService $jwt)
    {
        $this->jwt = $jwt;
    }

    public function handle($request, Closure $next)
    {
        $header = $request->header('Authorization', '');
        if (! preg_match('/Bearer\s(\S+)/', $header, $matches)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Token not provided'
            ], Response::HTTP_UNAUTHORIZED);
        }

        try {
            // Decode and validate the token
            $decoded = $this->jwt->validateToken($matches[1]);

            // Find the user by the subject claim
            $user = User::where('id', $decoded->sub)->firstOrFail();

            // Log the user into Laravel's Auth system
            Auth::login($user);

            // Ensure $request->user() returns this user
            $request->setUserResolver(fn() => $user);

            // Make the raw payload available if you need it
            $request->attributes->set('jwt_payload', $decoded);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Invalid token'
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
