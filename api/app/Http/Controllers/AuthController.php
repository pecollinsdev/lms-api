<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    /**
     * Register a new user and return a JWT.
     */
    public function register(Request $request)
    {
        $data = $this->validated($request, [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role'     => 'required|in:student,instructor',
            'phone_number' => 'required|string|max:20',
            'bio'      => 'nullable|string|max:500',
        ]);

        // Additional validation for instructors
        if ($data['role'] === 'instructor') {
            $instructorData = $this->validate($request, [
                'instructor_code' => 'required|string|exists:instructor_codes,code,used,0',
                'academic_specialty' => 'required|string|max:255',
                'qualifications' => 'required|string',
            ]);
            
            // Merge instructor fields into main data array
            $data = array_merge($data, $instructorData);
        }

        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);

        // If user is an instructor, mark the instructor code as used
        if ($user->role === 'instructor') {
            DB::table('instructor_codes')
                ->where('code', $data['instructor_code'])
                ->update(['used' => true]);
        }

        // Generate token
        $token = JWTAuth::fromUser($user);

        return $this->respondCreated(
            ['user' => $user],
            'Registration successful'
        )->cookie(
            'jwt_token',
            $token,
            config('jwt.ttl') * 60, // Convert minutes to seconds
            '/',
            null,
            true, // Secure
            true  // HttpOnly
        );
    }

    /**
     * Authenticate a user and return a JWT.
     */
    public function login(Request $request)
    {
        $data = $this->validated($request, [
            'email'    => 'required|email',
            'password' => 'required|string|min:8',
        ]);

        $user = User::where('email', $data['email'])->first();
        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return $this->respondUnauthorized('Invalid credentials');
        }

        // Generate token
        $token = JWTAuth::fromUser($user);

        return $this->respond(
            ['user' => $user],
            'Login successful'
        )->cookie(
            'jwt_token',
            $token,
            config('jwt.ttl') * 60,
            '/',
            null,
            true, // Secure
            true  // HttpOnly
        );
    }

    /**
     * Log out a user by invalidating their token.
     */
    public function logout(Request $request)
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
        } catch (\Exception $e) {
            // Token might already be invalid, continue with logout
        }

        return $this->respond(
            null,
            'Logged out successfully'
        )->cookie(
            'jwt_token',
            null,
            -1, // Expire immediately
            '/',
            null,
            true,
            true
        );
    }

    /**
     * Refresh the user's token.
     */
    public function refresh()
    {
        try {
            $token = JWTAuth::refresh();
            
            return $this->respond(
                null,
                'Token refreshed successfully'
            )->cookie(
                'jwt_token',
                $token,
                config('jwt.ttl') * 60,
                '/',
                null,
                true,
                true
            );
        } catch (\Exception $e) {
            return $this->respondUnauthorized('Could not refresh token');
        }
    }
}
