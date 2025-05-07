<?php

namespace Database\Factories;

use App\Models\Progress;
use App\Models\User;
use App\Models\Assignment;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProgressFactory extends Factory
{
    protected $model = Progress::class;

    public function definition()
    {
        return [
            'user_id'        => User::factory()->student(),
            'assignment_id'  => Assignment::factory(),
            'status'         => $this->faker->randomElement(['not_started','in_progress','completed']),
        ];
    }

    /** Mark this as completed */
    public function completed()
    {
        return $this->state(fn(array $attrs) => [
            'status' => 'completed',
        ]);
    }
}
