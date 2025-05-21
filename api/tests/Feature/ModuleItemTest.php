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

class ModuleItemTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;
    protected Course $course;
    protected Module $module;
    protected ModuleItem $moduleItem;

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

        // Create a test module item
        $this->moduleItem = ModuleItem::factory()->create([
            'module_id' => $this->module->id
        ]);

        // Authenticate the user
        $this->actingAs($this->user);
    }

    #[Test]
    public function it_can_list_module_items()
    {
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
        $moduleItemData = [
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'type' => 'video',
            'content' => [
                'video_url' => 'https://example.com/video.mp4',
                'video_provider' => 'youtube',
                'video_duration' => 300,
                'video_allow_download' => false
            ],
            'settings' => [
                'allow_comments' => true,
                'require_completion' => true
            ]
        ];

        $response = $this->postJson("/api/modules/{$this->module->id}/items", $moduleItemData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'description',
                    'module_id',
                    'type',
                    'content',
                    'settings',
                    'created_at',
                    'updated_at'
                ]
            ]);

        $this->assertDatabaseHas('module_items', [
            'title' => $moduleItemData['title'],
            'description' => $moduleItemData['description'],
            'type' => $moduleItemData['type']
        ]);
    }

    #[Test]
    public function it_can_show_a_module_item()
    {
        $response = $this->getJson("/api/module-items/{$this->moduleItem->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'description',
                    'module_id',
                    'type',
                    'content',
                    'settings',
                    'created_at',
                    'updated_at'
                ]
            ]);
    }

    #[Test]
    public function it_can_update_a_module_item()
    {
        $updateData = [
            'title' => 'Updated Module Item Title',
            'description' => 'Updated module item description',
            'type' => 'quiz',
            'content' => [
                'questions' => [
                    [
                        'question' => 'What is 2+2?',
                        'type' => 'multiple_choice',
                        'options' => ['3', '4', '5', '6'],
                        'correct_answer' => '4'
                    ]
                ],
                'time_limit' => 30,
                'passing_score' => 70
            ],
            'settings' => [
                'allow_retakes' => true,
                'show_correct_answers' => true
            ]
        ];

        $response = $this->putJson("/api/module-items/{$this->moduleItem->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'description',
                    'module_id',
                    'type',
                    'content',
                    'settings',
                    'created_at',
                    'updated_at'
                ]
            ]);

        $this->assertDatabaseHas('module_items', [
            'title' => $updateData['title'],
            'description' => $updateData['description'],
            'type' => $updateData['type']
        ]);
    }

    #[Test]
    public function it_can_delete_a_module_item()
    {
        $response = $this->deleteJson("/api/module-items/{$this->moduleItem->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('module_items', ['id' => $this->moduleItem->id]);
    }

    #[Test]
    public function it_can_reorder_module_items()
    {
        // Create additional module items
        $moduleItems = ModuleItem::factory()->count(3)->create([
            'module_id' => $this->module->id
        ]);

        $reorderData = [
            'items' => [
                ['id' => $moduleItems[2]->id, 'order' => 1],
                ['id' => $moduleItems[0]->id, 'order' => 2],
                ['id' => $moduleItems[1]->id, 'order' => 3]
            ]
        ];

        $response = $this->postJson("/api/modules/{$this->module->id}/items/reorder", $reorderData);

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
                        'settings',
                        'created_at',
                        'updated_at'
                    ]
                ]
            ]);

        // Verify the order was updated
        $this->assertEquals(1, ModuleItem::find($moduleItems[2]->id)->order);
        $this->assertEquals(2, ModuleItem::find($moduleItems[0]->id)->order);
        $this->assertEquals(3, ModuleItem::find($moduleItems[1]->id)->order);
    }
} 