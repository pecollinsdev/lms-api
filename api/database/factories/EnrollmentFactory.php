<?php

namespace Database\Factories;

use App\Models\Enrollment;
use App\Models\User;
use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

class EnrollmentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Enrollment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'     => User::factory()->state(['role' => 'student']),
            'course_id'   => Course::factory(),
            'enrolled_at' => now(),
            'status'      => $this->faker->randomElement(['pending','active','completed']),
        ];
    }

    /**
     * Indicate the enrollment is active.
     */
    public function active(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Indicate the enrollment is pending.
     */
    public function pending(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    /**
     * Indicate the enrollment is completed.
     */
    public function completed(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
        ]);
    }
}
