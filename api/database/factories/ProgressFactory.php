<?php

namespace Database\Factories;

use App\Models\Progress;
use App\Models\User;
use App\Models\ModuleItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProgressFactory extends Factory
{
    protected $model = Progress::class;

    public function definition()
    {
        $status = $this->faker->randomElement(['not_started', 'in_progress', 'completed']);
        
        return [
            'user_id' => User::factory(),
            'module_item_id' => ModuleItem::factory(),
            'status' => $status,
            'completed_at' => $status === 'completed' ? $this->faker->dateTimeThisMonth() : null,
            'last_accessed_at' => $this->faker->dateTimeThisMonth()
        ];
    }

    public function notStarted(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'not_started',
                'completed_at' => null,
            ];
        });
    }

    public function inProgress(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'in_progress',
                'completed_at' => null,
            ];
        });
    }

    public function submitted(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'submitted',
                'completed_at' => now(),
            ];
        });
    }

    public function graded(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'graded',
                'completed_at' => now(),
            ];
        });
    }
}
