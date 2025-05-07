<?php

namespace Database\Factories;

use App\Models\ModuleItem;
use App\Models\Module;
use Illuminate\Database\Eloquent\Factories\Factory;

class ModuleItemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ModuleItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'module_id' => Module::factory(),
            'type' => $this->faker->randomElement(['video', 'assignment', 'quiz', 'document']),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'due_date' => $this->faker->dateTimeBetween('+1 week', '+2 weeks'),
            'order' => $this->faker->numberBetween(0, 100),
        ];
    }

    /**
     * Indicate that the module item is a video.
     */
    public function video(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'video',
        ]);
    }

    /**
     * Indicate that the module item is an assignment.
     */
    public function assignment(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'assignment',
        ]);
    }

    /**
     * Indicate that the module item is a quiz.
     */
    public function quiz(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'quiz',
        ]);
    }

    /**
     * Indicate that the module item is a document.
     */
    public function document(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'document',
        ]);
    }

    /**
     * Indicate that the module item is due soon.
     */
    public function dueSoon(): static
    {
        return $this->state(fn (array $attributes) => [
            'due_date' => now()->addDays(3),
        ]);
    }

    /**
     * Indicate that the module item is past due.
     */
    public function pastDue(): static
    {
        return $this->state(fn (array $attributes) => [
            'due_date' => now()->subDays(3),
        ]);
    }
} 