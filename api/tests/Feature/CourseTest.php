<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Course;
use App\Models\Module;
use App\Models\ModuleItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;

class CourseTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;
    protected Course $course;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a test user
        $this->user = User::factory()->create([
            'role' => 'instructor'
        ]);

        // Create a test course
        $this->course = Course::factory()->create([
            'instructor_id' => $this->user->id
        ]);

        // Authenticate the user
        $this->actingAs($this->user);
    }

    #[Test]
    public function it_can_list_courses()
    {
        $response = $this->getJson('/api/courses');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'description',
                        'instructor_id',
                        'created_at',
                        'updated_at'
                    ]
                ]
            ]);
    }

    #[Test]
    public function it_can_create_a_course()
    {
        $courseData = [
            'title' => $this->faker->sentence,
            'slug' => $this->faker->slug,
            'description' => $this->faker->paragraph,
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addMonths(3)->format('Y-m-d'),
            'is_published' => true,
            'cover_image' => null
        ];

        $response = $this->postJson('/api/courses', $courseData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'slug',
                    'description',
                    'start_date',
                    'end_date',
                    'is_published',
                    'cover_image',
                    'instructor_id',
                    'created_at',
                    'updated_at'
                ]
            ]);

        $this->assertDatabaseHas('courses', $courseData);
    }

    #[Test]
    public function it_can_show_a_course()
    {
        $response = $this->getJson("/api/courses/{$this->course->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'description',
                    'instructor_id',
                    'created_at',
                    'updated_at'
                ]
            ]);
    }

    #[Test]
    public function it_can_update_a_course()
    {
        $updateData = [
            'title' => 'Updated Course Title',
            'slug' => 'updated-course-title',
            'description' => 'Updated course description',
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addMonths(3)->format('Y-m-d'),
            'is_published' => true,
            'cover_image' => null
        ];

        $response = $this->putJson("/api/courses/{$this->course->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'slug',
                    'description',
                    'start_date',
                    'end_date',
                    'is_published',
                    'cover_image',
                    'instructor_id',
                    'created_at',
                    'updated_at'
                ]
            ]);

        $this->assertDatabaseHas('courses', $updateData);
    }

    #[Test]
    public function it_can_delete_a_course()
    {
        $response = $this->deleteJson("/api/courses/{$this->course->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('courses', ['id' => $this->course->id]);
    }

    #[Test]
    public function it_can_list_course_modules()
    {
        // Create some modules for the course
        Module::factory()->count(3)->create([
            'course_id' => $this->course->id
        ]);

        $response = $this->getJson("/api/courses/{$this->course->id}/modules");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'description',
                        'course_id',
                        'created_at',
                        'updated_at'
                    ]
                ]
            ]);
    }

    #[Test]
    public function it_can_list_course_module_items()
    {
        // Create a module and module items
        $module = Module::factory()->create([
            'course_id' => $this->course->id
        ]);

        ModuleItem::factory()->count(3)->create([
            'module_id' => $module->id
        ]);

        $response = $this->getJson("/api/courses/{$this->course->id}/module-items");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'description',
                        'module_id',
                        'type',
                        'content',
                        'created_at',
                        'updated_at'
                    ]
                ]
            ]);
    }
} 