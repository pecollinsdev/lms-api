<?php

namespace Database\Factories;

use App\Models\Grade;
use App\Models\Submission;
use Illuminate\Database\Eloquent\Factories\Factory;

class GradeFactory extends Factory
{
    protected $model = Grade::class;

    public function definition()
    {
        return [
            'submission_id' => Submission::factory(),
            'score' => $this->faker->numberBetween(0, 100),
            'feedback' => $this->faker->paragraph,
            'graded_by' => $this->faker->name,
            'graded_at' => $this->faker->dateTimeThisMonth()
        ];
    }
} 