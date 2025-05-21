<?php

namespace Database\Factories;

use App\Models\Submission;
use App\Models\User;
use App\Models\ModuleItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubmissionFactory extends Factory
{
    protected $model = Submission::class;

    public function definition()
    {
        $status = $this->faker->randomElement(['draft', 'submitted', 'in_review', 'graded']);
        
        return [
            'user_id' => User::factory(),
            'module_item_id' => ModuleItem::factory(),
            'content' => [
                'answers' => [
                    [
                        'question_id' => 1,
                        'answer' => $this->faker->word
                    ]
                ]
            ],
            'status' => $status,
            'submitted_at' => $status !== 'draft' ? $this->faker->dateTimeThisMonth() : null
        ];
    }

    public function pending(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'pending',
            ];
        });
    }

    public function graded(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'graded',
            ];
        });
    }
}