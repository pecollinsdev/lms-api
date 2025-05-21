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

class ModuleTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;
    protected Course $course;
    protected Module $module;

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

        // Create a test module
        $this->module = Module::factory()->create([
            'course_id' => $this->course->id
        ]);

        // Authenticate the user
        $this->actingAs($this->user);
    }

    #[Test]
    public function it_can_list_modules()
    {
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
    public function it_can_create_a_module()
    {
        $moduleData = [
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph
        ];

        $response = $this->postJson("/api/courses/{$this->course->id}/modules", $moduleData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'description',
                    'course_id',
                    'created_at',
                    'updated_at'
                ]
            ]);

        $this->assertDatabaseHas('modules', [
            'title' => $moduleData['title'],
            'description' => $moduleData['description'],
            'course_id' => $this->course->id
        ]);
    }

    #[Test]
    public function it_can_show_a_module()
    {
        $response = $this->getJson("/api/modules/{$this->module->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'description',
                    'course_id',
                    'created_at',
                    'updated_at'
                ]
            ]);
    }

    #[Test]
    public function it_can_update_a_module()
    {
        $updateData = [
            'title' => 'Updated Module Title',
            'description' => 'Updated module description'
        ];

        $response = $this->putJson("/api/modules/{$this->module->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'description',
                    'course_id',
                    'created_at',
                    'updated_at'
                ]
            ]);

        $this->assertDatabaseHas('modules', [
            'id' => $this->module->id,
            'title' => $updateData['title'],
            'description' => $updateData['description']
        ]);
    }

    #[Test]
    public function it_can_delete_a_module()
    {
        $response = $this->deleteJson("/api/modules/{$this->module->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('modules', ['id' => $this->module->id]);
    }

    #[Test]
    public function it_can_list_module_items()
    {
        // Create some module items
        ModuleItem::factory()->count(3)->create([
            'module_id' => $this->module->id
        ]);

        $response = $this->getJson("/api/modules/{$this->module->id}/items");

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

    #[Test]
    public function it_can_create_a_module_item()
    {
        $itemData = [
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'module_id' => $this->module->id,
            'type' => 'video',
            'content' => [
                'url' => 'https://example.com/video.mp4',
                'duration' => '10:00'
            ]
        ];

        $response = $this->postJson("/api/modules/{$this->module->id}/items", $itemData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'description',
                    'module_id',
                    'type',
                    'content',
                    'created_at',
                    'updated_at'
                ]
            ]);

        $this->assertDatabaseHas('module_items', [
            'title' => $itemData['title'],
            'description' => $itemData['description'],
            'module_id' => $this->module->id,
            'type' => 'video'
        ]);
    }

    #[Test]
    public function it_can_reorder_modules()
    {
        // Create additional modules
        $modules = Module::factory()->count(3)->create([
            'course_id' => $this->course->id
        ]);

        $reorderData = [
            'modules' => [
                ['id' => $modules[2]->id, 'order' => 1],
                ['id' => $modules[0]->id, 'order' => 2],
                ['id' => $modules[1]->id, 'order' => 3]
            ]
        ];

        $response = $this->postJson("/api/courses/{$this->course->id}/modules/reorder", $reorderData);

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

        // Verify the order was updated
        $this->assertEquals(1, Module::find($modules[2]->id)->order);
        $this->assertEquals(2, Module::find($modules[0]->id)->order);
        $this->assertEquals(3, Module::find($modules[1]->id)->order);
    }
} 