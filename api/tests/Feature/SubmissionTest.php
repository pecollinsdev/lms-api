<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Course;
use App\Models\Module;
use App\Models\ModuleItem;
use App\Models\Submission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;

class SubmissionTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;
    protected Course $course;
    protected Module $module;
    protected ModuleItem $moduleItem;
    protected Submission $submission;

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
            'module_id' => $this->module->id,
            'type' => 'assignment'
        ]);

        // Create a test submission
        $this->submission = Submission::factory()->create([
            'user_id' => $this->user->id,
            'module_item_id' => $this->moduleItem->id,
            'status' => 'submitted',
            'score' => null,
            'feedback' => null,
            'submitted_at' => now()
        ]);

        // Authenticate the user
        $this->actingAs($this->user);
    }

    #[Test]
    public function it_can_list_user_submissions()
    {
        $response = $this->getJson('/api/submissions');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'user_id',
                        'module_item_id',
                        'status',
                        'score',
                        'feedback',
                        'submitted_at',
                        'created_at',
                        'updated_at'
                    ]
                ]
            ]);
    }

    #[Test]
    public function it_can_create_submission()
    {
        $submissionData = [
            'module_item_id' => $this->moduleItem->id,
            'content' => [
                'answers' => [
                    [
                        'question' => 'What is 2+2?',
                        'answer' => '4'
                    ]
                ]
            ],
            'status' => 'submitted',
            'submitted_at' => now()
        ];

        $response = $this->postJson('/api/submissions', $submissionData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'user_id',
                    'module_item_id',
                    'status',
                    'score',
                    'feedback',
                    'submitted_at',
                    'created_at',
                    'updated_at'
                ]
            ]);

        $this->assertDatabaseHas('submissions', [
            'user_id' => $this->user->id,
            'module_item_id' => $submissionData['module_item_id'],
            'status' => $submissionData['status']
        ]);
    }

    #[Test]
    public function it_can_show_submission()
    {
        $response = $this->getJson("/api/submissions/{$this->submission->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'user_id',
                    'module_item_id',
                    'status',
                    'score',
                    'feedback',
                    'submitted_at',
                    'created_at',
                    'updated_at'
                ]
            ]);
    }

    #[Test]
    public function it_can_update_submission()
    {
        $updateData = [
            'status' => 'graded',
            'score' => 85,
            'feedback' => 'Good work!'
        ];

        $response = $this->putJson("/api/submissions/{$this->submission->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'user_id',
                    'module_item_id',
                    'status',
                    'score',
                    'feedback',
                    'submitted_at',
                    'created_at',
                    'updated_at'
                ]
            ]);

        $this->assertDatabaseHas('submissions', [
            'id' => $this->submission->id,
            'status' => $updateData['status'],
            'score' => $updateData['score'],
            'feedback' => $updateData['feedback']
        ]);
    }

    #[Test]
    public function it_can_delete_submission()
    {
        $response = $this->deleteJson("/api/submissions/{$this->submission->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('submissions', ['id' => $this->submission->id]);
    }

    #[Test]
    public function it_can_list_module_item_submissions()
    {
        $response = $this->getJson("/api/module-items/{$this->moduleItem->id}/submissions");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'user_id',
                        'module_item_id',
                        'status',
                        'score',
                        'feedback',
                        'submitted_at',
                        'created_at',
                        'updated_at'
                    ]
                ]
            ]);
    }
} 