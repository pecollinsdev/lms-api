<?php

namespace App\Http\Controllers;

use App\Services\JwtService;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    protected JwtService $jwt;

    public function __construct(JwtService $jwt)
    {
        $this->jwt = $jwt;
    }

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

        $token = $this->jwt->generateToken([
            'sub'  => $user->id,
            'role' => $user->role,
        ]);

        return $this->respondCreated(
            ['token' => $token, 'user' => $user],
            'Registration successful'
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

        $token = $this->jwt->generateToken([
            'sub'  => $user->id,
            'role' => $user->role,
        ]);

        return $this->respond(
            ['token' => $token, 'user' => $user],
            'Login successful'
        );
    }

    /**
     * “Log out” a user.
     * For stateless JWT, instruct the client to discard the token.
     */
    public function logout(Request $request)
    {
        return $this->respond(null, 'Logged out successfully');
    }
}
