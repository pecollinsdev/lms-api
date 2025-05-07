<?php

namespace Database\Factories;

use App\Models\Module;
use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

class ModuleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Module::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'start_date' => $this->faker->dateTimeBetween('-1 month', '+1 month'),
            'end_date' => $this->faker->dateTimeBetween('+1 month', '+3 months'),
        ];
    }

    /**
     * Indicate that the module is currently active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'start_date' => now()->subDays(7),
            'end_date' => now()->addDays(7),
        ]);
    }

    /**
     * Indicate that the module is upcoming.
     */
    public function upcoming(): static
    {
        return $this->state(fn (array $attributes) => [
            'start_date' => now()->addDays(7),
            'end_date' => now()->addDays(21),
        ]);
    }

    /**
     * Indicate that the module is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'start_date' => now()->subDays(21),
            'end_date' => now()->subDays(7),
        ]);
    }
} 