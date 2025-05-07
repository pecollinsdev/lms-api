<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a test instructor code
        DB::table('instructor_codes')->insert([
            'code' => 'TEST123',
            'used' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /** @test */
    public function user_can_register_as_student()
    {
        $response = $this->postJson('/register', [
            'name' => 'Test Student',
            'email' => 'student@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'student',
            'phone_number' => '1234567890',
            'bio' => 'Test bio'
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Registration successful'
            ])
            ->assertCookie('jwt_token');

        $this->assertDatabaseHas('users', [
            'email' => 'student@test.com',
            'role' => 'student'
        ]);
    }

    /** @test */
    public function user_can_register_as_instructor()
    {
        $response = $this->postJson('/register', [
            'name' => 'Test Instructor',
            'email' => 'instructor@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'instructor',
            'phone_number' => '1234567890',
            'bio' => 'Test bio',
            'instructor_code' => 'TEST123',
            'academic_specialty' => 'Computer Science',
            'qualifications' => 'PhD'
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Registration successful'
            ])
            ->assertCookie('jwt_token');

        $this->assertDatabaseHas('users', [
            'email' => 'instructor@test.com',
            'role' => 'instructor'
        ]);

        $this->assertDatabaseHas('instructor_codes', [
            'code' => 'TEST123',
            'used' => true
        ]);
    }

    /** @test */
    public function user_can_login()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'role' => 'student',
            'phone_number' => '1234567890'
        ]);

        $response = $this->postJson('/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Login successful'
            ])
            ->assertCookie('jwt_token');
    }

    /** @test */
    public function user_cannot_login_with_invalid_credentials()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'role' => 'student',
            'phone_number' => '1234567890'
        ]);

        $response = $this->postJson('/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Invalid credentials'
            ])
            ->assertCookieMissing('jwt_token');
    }

    /** @test */
    public function user_can_access_protected_route_with_valid_token()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'role' => 'student',
            'phone_number' => '1234567890'
        ]);

        $response = $this->postJson('/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $token = $response->getCookie('jwt_token')->getValue();

        $response = $this->withCookie('jwt_token', $token)
            ->getJson('/me');

        $response->assertStatus(200);
    }

    /** @test */
    public function user_cannot_access_protected_route_without_token()
    {
        $response = $this->getJson('/me');

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Token not provided'
            ]);
    }

    /** @test */
    public function user_can_logout()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'role' => 'student',
            'phone_number' => '1234567890'
        ]);

        $response = $this->postJson('/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $token = $response->getCookie('jwt_token')->getValue();

        $response = $this->withCookie('jwt_token', $token)
            ->postJson('/logout');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Logged out successfully'
            ])
            ->assertCookieExpired('jwt_token');

        // Verify can't access protected route after logout
        $response = $this->withCookie('jwt_token', $token)
            ->getJson('/me');

        $response->assertStatus(401);
    }

    /** @test */
    public function user_can_refresh_token()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'role' => 'student',
            'phone_number' => '1234567890'
        ]);

        $response = $this->postJson('/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $token = $response->getCookie('jwt_token')->getValue();

        $response = $this->withCookie('jwt_token', $token)
            ->postJson('/refresh');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Token refreshed successfully'
            ])
            ->assertCookie('jwt_token');

        // Verify new token works
        $newToken = $response->getCookie('jwt_token')->getValue();
        $response = $this->withCookie('jwt_token', $newToken)
            ->getJson('/me');

        $response->assertStatus(200);
    }
}
