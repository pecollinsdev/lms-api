<?php

namespace Database\Factories;

use App\Models\Question;
use App\Models\ModuleItem;
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
        return [
            'module_item_id' => ModuleItem::factory(),
            'question_text' => $this->faker->sentence(),
            'question_type' => $this->faker->randomElement(['multiple_choice', 'true_false', 'short_answer']),
            'points' => $this->faker->numberBetween(1, 10),
            'order' => $this->faker->numberBetween(1, 20),
        ];
    }

    /**
     * Indicate a multiple-choice question.
     */
    public function multipleChoice(): self
    {
        return $this->state(fn (array $attributes) => [
            'question_type' => 'multiple_choice',
            'question_text' => $this->faker->sentence(6),
            'settings' => ['shuffle' => $this->faker->boolean()],
        ]);
    }

    /**
     * Indicate a text question.
     */
    public function text(): self
    {
        return $this->state(fn (array $attributes) => [
            'question_type' => 'text',
            'question_text' => $this->faker->paragraph(),
            'settings' => null,
        ]);
    }
}
