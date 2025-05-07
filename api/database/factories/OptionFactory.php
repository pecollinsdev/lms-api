<?php

namespace Database\Factories;

use App\Models\Option;
use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

class OptionFactory extends Factory
{
    protected $model = Option::class;

    public function definition(): array
    {
        return [
            'question_id' => Question::factory(),
            'text'        => $this->faker->sentence(4),
            'order'       => $this->faker->numberBetween(0, 5),
            'is_correct'  => false,
        ];
    }

    public function correct(): self
    {
        return $this->state(fn(array $attributes) => [
            'is_correct' => true
        ]);
    }
}