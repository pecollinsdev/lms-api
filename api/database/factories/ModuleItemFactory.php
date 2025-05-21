<?php

namespace Database\Factories;

use App\Models\ModuleItem;
use App\Models\Module;
use Illuminate\Database\Eloquent\Factories\Factory;

class ModuleItemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ModuleItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = $this->faker->randomElement(['video', 'document', 'quiz', 'assignment']);
        $contentData = [];
        $settings = [];

        switch ($type) {
            case 'video':
                $contentData = [
                    'video_url' => $this->faker->url,
                    'video_provider' => $this->faker->randomElement(['youtube', 'vimeo']),
                    'video_duration' => $this->faker->numberBetween(60, 3600),
                    'video_allow_download' => $this->faker->boolean,
                ];
                break;
            case 'document':
                $contentData = [
                    'document_url' => $this->faker->url,
                    'document_type' => $this->faker->randomElement(['pdf', 'doc', 'docx']),
                    'document_size' => $this->faker->numberBetween(100, 10000),
                    'document_allow_download' => $this->faker->boolean,
                ];
                break;
            case 'quiz':
                $contentData = [
                    'quiz_instructions' => $this->faker->paragraph,
                ];
                $settings = [
                    'max_attempts' => $this->faker->numberBetween(1, 3),
                    'show_correct_answers' => $this->faker->boolean,
                    'show_feedback' => $this->faker->boolean,
                    'passing_score' => $this->faker->numberBetween(60, 100),
                ];
                break;
            case 'assignment':
                $contentData = [
                    'assignment_instructions' => $this->faker->paragraph,
                ];
                $settings = [
                    'max_attempts' => $this->faker->numberBetween(1, 3),
                    'allow_late_submission' => $this->faker->boolean,
                    'late_submission_penalty' => $this->faker->numberBetween(5, 20),
                ];
                break;
        }

        return [
            'module_id' => Module::factory(),
            'type' => $type,
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'due_date' => $this->faker->dateTimeBetween('now', '+1 month'),
            'order' => $this->faker->numberBetween(1, 100),
            'max_score' => $this->faker->numberBetween(10, 100),
            'submission_type' => $type === 'quiz' ? 'multiple_choice' : 'file',
            'content_data' => $contentData,
            'settings' => $settings,
        ];
    }

    /**
     * Indicate that the module item is a video.
     */
    public function video(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'video',
            'content_data' => [
                'video_url' => $this->faker->url,
                'video_provider' => 'youtube',
                'video_duration' => $this->faker->time('H:i:s'),
                'video_allow_download' => false
            ]
        ]);
    }

    /**
     * Indicate that the module item is an assignment.
     */
    public function assignment(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'assignment',
            'content_data' => [
                'assignment_instructions' => $this->faker->paragraph
            ]
        ]);
    }

    /**
     * Indicate that the module item is a quiz.
     */
    public function quiz(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'quiz',
            'content_data' => [
                'questions' => [
                    [
                        'question' => $this->faker->sentence . '?',
                        'options' => $this->faker->words(4),
                        'correct_answer' => $this->faker->word
                    ]
                ]
            ],
            'submission_type' => 'multiple_choice'
        ]);
    }

    /**
     * Indicate that the module item is a document.
     */
    public function document(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'document',
            'content_data' => [
                'document_url' => $this->faker->url,
                'document_type' => 'pdf',
                'document_size' => $this->faker->numberBetween(1, 50) . 'MB',
                'document_allow_download' => true
            ]
        ]);
    }

    /**
     * Indicate that the module item is due soon.
     */
    public function dueSoon(): static
    {
        return $this->state(fn (array $attributes) => [
            'due_date' => now()->addDays(3),
        ]);
    }

    /**
     * Indicate that the module item is past due.
     */
    public function pastDue(): static
    {
        return $this->state(fn (array $attributes) => [
            'due_date' => now()->subDays(3),
        ]);
    }
} 