<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_registers_a_new_student_and_returns_a_token()
    {
        $payload = [
            'name'                  => 'Jane Doe',
            'email'                 => 'jane@example.com',
            'password'              => 'secret123',
            'password_confirmation' => 'secret123',
            'role'                  => 'student',
        ];

        $response = $this->postJson('/api/register', $payload);

        $response->assertCreated()
                 ->assertJsonStructure([
                     'status',
                     'message',
                     'data' => [
                         'token',
                         'user' => ['id','name','email','role']
                     ]
                 ]);

        $this->assertDatabaseHas('users', [
            'email' => 'jane@example.com',
            'role'  => 'student'
        ]);
    }

    /** @test */
    public function login_with_invalid_credentials_returns_unauthorized()
    {
        User::factory()->create([
            'email'    => 'john@example.com',
            'password' => bcrypt('correcthorsebatterystaple'),
        ]);

        $response = $this->postJson('/api/login', [
            'email'    => 'john@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertUnauthorized()
                 ->assertJson(['status' => 'error','message' => 'Invalid credentials']);
    }
}
