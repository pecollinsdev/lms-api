<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Course;
use App\Models\Module;
use App\Models\ModuleItem;
use App\Models\Progress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;

class ProgressTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;
    protected Course $course;
    protected Module $module;
    protected ModuleItem $moduleItem;
    protected Progress $progress;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a test user
        $this->user = User::factory()->create([
            'role' => 'student'
        ]);

        // Create a test course
        $this->course = Course::factory()->create();

        // Create a test module
        $this->module = Module::factory()->create([
            'course_id' => $this->course->id
        ]);

        // Create a test module item
        $this->moduleItem = ModuleItem::factory()->create([
            'module_id' => $this->module->id
        ]);

        // Create a test progress
        $this->progress = Progress::factory()->create([
            'user_id' => $this->user->id,
            'module_item_id' => $this->moduleItem->id,
            'status' => 'in_progress',
            'score' => null,
            'completed_at' => null
        ]);

        // Authenticate the user
        $this->actingAs($this->user);
    }

    #[Test]
    public function it_can_list_user_progress()
    {
        $response = $this->getJson('/api/progress');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'user_id',
                        'module_item_id',
                        'status',
                        'score',
                        'completed_at',
                        'created_at',
                        'updated_at'
                    ]
                ]
            ]);
    }

    #[Test]
    public function it_can_create_progress()
    {
        $progressData = [
            'module_item_id' => $this->moduleItem->id,
            'status' => 'in_progress'
        ];

        $response = $this->postJson('/api/progress', $progressData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'user_id',
                    'module_item_id',
                    'status',
                    'score',
                    'completed_at',
                    'created_at',
                    'updated_at'
                ]
            ]);

        $this->assertDatabaseHas('progress', [
            'user_id' => $this->user->id,
            'module_item_id' => $progressData['module_item_id'],
            'status' => $progressData['status']
        ]);
    }

    #[Test]
    public function it_can_show_progress()
    {
        $response = $this->getJson("/api/progress/{$this->progress->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'user_id',
                    'module_item_id',
                    'status',
                    'score',
                    'completed_at',
                    'created_at',
                    'updated_at'
                ]
            ]);
    }

    #[Test]
    public function it_can_update_progress()
    {
        $updateData = [
            'status' => 'completed',
            'score' => 85,
            'completed_at' => now()
        ];

        $response = $this->putJson("/api/progress/{$this->progress->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'user_id',
                    'module_item_id',
                    'status',
                    'score',
                    'completed_at',
                    'created_at',
                    'updated_at'
                ]
            ]);

        $this->assertDatabaseHas('progress', [
            'id' => $this->progress->id,
            'status' => $updateData['status'],
            'score' => $updateData['score']
        ]);
    }

    #[Test]
    public function it_can_delete_progress()
    {
        $response = $this->deleteJson("/api/progress/{$this->progress->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('progress', ['id' => $this->progress->id]);
    }

    #[Test]
    public function it_can_list_course_progress()
    {
        $response = $this->getJson("/api/courses/{$this->course->id}/progress");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'user_id',
                        'module_item_id',
                        'status',
                        'score',
                        'completed_at',
                        'created_at',
                        'updated_at'
                    ]
                ]
            ]);
    }

    #[Test]
    public function it_can_list_module_progress()
    {
        $response = $this->getJson("/api/modules/{$this->module->id}/progress");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'user_id',
                        'module_item_id',
                        'status',
                        'score',
                        'completed_at',
                        'created_at',
                        'updated_at'
                    ]
                ]
            ]);
    }
} 