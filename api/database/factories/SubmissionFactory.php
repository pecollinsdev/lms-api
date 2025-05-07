<?php

namespace Database\Factories;

use App\Models\Submission;
use App\Models\User;
use App\Models\Assignment;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubmissionFactory extends Factory
{
    protected $model = Submission::class;

    public function definition()
    {
        return [
            'user_id'       => User::factory(),
            'assignment_id' => Assignment::factory(),
        ];
    }
}