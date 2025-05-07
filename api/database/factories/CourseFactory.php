<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CourseFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Course::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->sentence(4);
        $start = $this->faker->date();
        $end   = $this->faker->dateTimeBetween($start.' +1 week', $start.' +1 month')->format('Y-m-d');

        return [
            'title'         => $title,
            'slug'          => Str::slug($title) . '-' . $this->faker->unique()->numberBetween(1, 1000),
            'description'   => $this->faker->paragraph(),
            'instructor_id' => User::factory()->state(['role' => 'instructor']),
            'start_date'    => $start,
            'end_date'      => $end,
            'is_published'  => $this->faker->boolean(70),
            'cover_image'   => null,
        ];
    }

    /**
     * Indicate the course is published.
     */
    public function published(): self
    {
        return $this->state(fn (array $attributes) => ['is_published' => true]);
    }

    /**
     * Indicate the course is unpublished.
     */
    public function unpublished(): self
    {
        return $this->state(fn (array $attributes) => ['is_published' => false]);
    }
}