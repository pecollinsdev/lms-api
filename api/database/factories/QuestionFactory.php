<?php

namespace Database\Factories;

use App\Models\Question;
use App\Models\Assignment;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuestionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Question::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Randomly choose question type
        $type = $this->faker->randomElement(['multiple_choice', 'text']);

        return [
            'assignment_id' => Assignment::factory(),
            'type'          => $type,
            'prompt'        => $this->faker->sentence(6),
            'order'         => $this->faker->numberBetween(0, 10),
            'points'        => $this->faker->randomFloat(2, 1, 10),
            'settings'      => null,
        ];
    }

    /**
     * Indicate a multiple-choice question.
     */
    public function multipleChoice(): self
    {
        return $this->state(fn (array $attributes) => [
            'type'     => 'multiple_choice',
            'prompt'   => $this->faker->sentence(6),
            'settings' => ['shuffle' => $this->faker->boolean()],
        ]);
    }

    /**
     * Indicate a text question.
     */
    public function text(): self
    {
        return $this->state(fn (array $attributes) => [
            'type'     => 'text',
            'prompt'   => $this->faker->paragraph(),
            'settings' => null,
        ]);
    }
}
