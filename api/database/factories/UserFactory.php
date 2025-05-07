<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * The name of the factory’s corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * The current password being used by the factory.
     *
     * @var string|null
     */
    protected static ?string $password;

    /**
     * Define the model’s default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'             => $this->faker->name(),
            'email'            => $this->faker->unique()->safeEmail(),
            'email_verified_at'=> now(),
            // reuse the same hashed password for all users
            'password'         => static::$password ??= Hash::make('password'),
            'role'             => 'student',                  // default role
            'bio'              => $this->faker->sentence(),   // short user bio
            'profile_picture'  => null,                       // or store a dummy path
            'remember_token'   => Str::random(10),
        ];
    }

    /**
     * Indicate that the model’s email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    
    /**
     * State: make this user a student.
     */
    public function student()
    {
        return $this->state(fn(array $attrs) => [
            'role' => 'student',
        ]);
    }

    /**
     * State: make this user an instructor.
     */
    public function instructor()
    {
        return $this->state(fn(array $attrs) => [
            'role' => 'instructor',
        ]);
    }

    /**
     * State: make this user an admin.
     */
    public function admin()
    {
        return $this->state(fn(array $attrs) => [
            'role' => 'admin',
        ]);
    }
}
