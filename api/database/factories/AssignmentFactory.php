<?php

namespace Database\Factories;

use App\Models\Assignment;
use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AssignmentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Assignment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->sentence(3);

        return [
            'course_id'       => Course::factory(),
            'title'           => $title,
            'description'     => $this->faker->paragraph(),
            'due_date'        => $this->faker->dateTimeBetween('now', '+2 weeks'),
            'max_score'       => $this->faker->numberBetween(10, 100),
            'submission_type' => $this->faker->randomElement(['file','essay','quiz']),
            'settings'        => null,
        ];
    }

    /**
     * Indicate the assignment accepts file uploads.
     */
    public function file(): self
    {
        return $this->state(fn(array $attributes) => ['submission_type' => 'file']);
    }

    /**
     * Indicate the assignment is an essay.
     */
    public function essay(): self
    {
        return $this->state(fn(array $attributes) => ['submission_type' => 'essay']);
    }

    /**
     * Indicate the assignment is a quiz.
     */
    public function quiz(): self
    {
        return $this->state(fn(array $attributes) => ['submission_type' => 'quiz']);
    }
}