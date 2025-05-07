<?php

namespace Database\Factories;

use App\Models\Answer;
use App\Models\Question;
use App\Models\User;
use App\Models\Assignment;
use Illuminate\Database\Eloquent\Factories\Factory;

class AnswerFactory extends Factory
{
    protected $model = Answer::class;

    public function definition(): array
    {
        return [
            'user_id'            => User::factory()->state(['role'=>'student']),
            'assignment_id'      => Assignment::factory(),
            'question_id'        => Question::factory(),
            'answer_text'        => $this->faker->paragraph(),
            'selected_option_id' => null,
        ];
    }

    public function withOption(): self
    {
        return $this->state(fn() => [
            'selected_option_id' => \App\Models\Option::factory(),
        ]);
    }
}
