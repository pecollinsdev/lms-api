<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Assignment;
use App\Services\JwtService;
use Symfony\Component\HttpFoundation\Response;

class AssignmentTest extends TestCase
{
    use RefreshDatabase;

    protected JwtService $jwt;

    protected function setUp(): void
    {
        parent::setUp();
        $this->jwt = $this->app->make(JwtService::class);
    }

    public function test_instructor_can_create_assignment(): void
    {
        $instructor = User::factory()->create(['role' => 'instructor']);
        $course = Course::factory()->create(['instructor_id' => $instructor->id]);

        $payload = [
            'title'           => 'Test Assignment',
            'description'     => 'Describe the assignment.',
            'due_date'        => now()->addDays(3)->toDateTimeString(),
            'max_score'       => 50,
            'submission_type' => 'file',
        ];

        $token = $this->jwt->generateToken([
            'sub'  => $instructor->id,
            'role' => $instructor->role,
        ]);

        $response = $this
            ->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/courses/{$course->id}/assignments", $payload);

        $response
            ->assertCreated()
            ->assertJsonPath('data.title', $payload['title']);

        $this->assertDatabaseHas('assignments', [
            'course_id' => $course->id,
            'title'     => $payload['title'],
        ]);
    }

    public function test_student_cannot_create_assignment(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $instructor = User::factory()->create(['role' => 'instructor']);
        $course = Course::factory()->create(['instructor_id' => $instructor->id]);

        $token = $this->jwt->generateToken([
            'sub'  => $student->id,
            'role' => $student->role,
        ]);

        $response = $this
            ->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/courses/{$course->id}/assignments", [
                'title' => 'Should Fail',
                'due_date' => now()->toDateTimeString(),
            ]);

        $response->assertForbidden();
    }

    public function test_student_can_list_assignments_for_enrolled_course(): void
    {
        $instructor = User::factory()->create(['role' => 'instructor']);
        $course = Course::factory()->create(['instructor_id' => $instructor->id]);
        $assignment = $course->assignments()->create([
            'title'           => 'Enrolled Assignment',
            'description'     => '',
            'due_date'        => now()->addDay(),
            'max_score'       => 100,
            'submission_type' => 'quiz',
        ]);

        $student = User::factory()->create(['role' => 'student']);
        Enrollment::factory()->create([
            'user_id'   => $student->id,
            'course_id' => $course->id,
        ]);

        $token = $this->jwt->generateToken([
            'sub'  => $student->id,
            'role' => $student->role,
        ]);

        $response = $this
            ->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson("/api/courses/{$course->id}/assignments");

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.title', $assignment->title);
    }

    public function test_instructor_can_update_assignment(): void
    {
        $instructor = User::factory()->create(['role' => 'instructor']);
        $course = Course::factory()->create(['instructor_id' => $instructor->id]);
        $assignment = $course->assignments()->create([
            'title'           => 'Old Title',
            'description'     => '',
            'due_date'        => now()->addDay(),
            'max_score'       => 20,
            'submission_type' => 'essay',
        ]);

        $token = $this->jwt->generateToken([
            'sub'  => $instructor->id,
            'role' => $instructor->role,
        ]);

        $update = ['title' => 'Updated Title'];

        $response = $this
            ->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson("/api/assignments/{$assignment->id}", $update);

        $response
            ->assertOk()
            ->assertJsonPath('data.title', $update['title']);

        $this->assertDatabaseHas('assignments', [
            'id'    => $assignment->id,
            'title' => $update['title'],
        ]);
    }

    public function test_student_cannot_update_assignment(): void
    {
        $instructor = User::factory()->create(['role' => 'instructor']);
        $course = Course::factory()->create(['instructor_id' => $instructor->id]);
        $assignment = $course->assignments()->create([
            'title'           => 'Immutable',
            'description'     => '',
            'due_date'        => now()->addDay(),
            'max_score'       => 10,
            'submission_type' => 'file',
        ]);

        $student = User::factory()->create(['role' => 'student']);
        $token = $this->jwt->generateToken([
            'sub'  => $student->id,
            'role' => $student->role,
        ]);

        $response = $this
            ->withHeader('Authorization', 'Bearer ' . $token)
            ->patchJson("/api/assignments/{$assignment->id}", [
                'title' => 'Hack Title',
            ]);

        $response->assertForbidden();
    }

    public function test_instructor_can_delete_assignment(): void
    {
        $instructor = User::factory()->create(['role' => 'instructor']);
        $course = Course::factory()->create(['instructor_id' => $instructor->id]);
        $assignment = $course->assignments()->create([
            'title'           => 'To Delete',
            'description'     => '',
            'due_date'        => now()->addDay(),
            'max_score'       => 5,
            'submission_type' => 'essay',
        ]);

        $token = $this->jwt->generateToken([
            'sub'  => $instructor->id,
            'role' => $instructor->role,
        ]);

        $response = $this
            ->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson("/api/assignments/{$assignment->id}");

        $response->assertNoContent();

        // Soft delete assertion
        $this->assertSoftDeleted('assignments', [
            'id' => $assignment->id,
        ]);
    }

    public function test_student_cannot_delete_assignment(): void
    {
        $instructor = User::factory()->create(['role' => 'instructor']);
        $course = Course::factory()->create(['instructor_id' => $instructor->id]);
        $assignment = $course->assignments()->create([
            'title'           => 'Protected',
            'description'     => '',
            'due_date'        => now()->addDay(),
            'max_score'       => 5,
            'submission_type' => 'quiz',
        ]);

        $student = User::factory()->create(['role' => 'student']);
        $token = $this->jwt->generateToken([
            'sub'  => $student->id,
            'role' => $student->role,
        ]);

        $response = $this
            ->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson("/api/assignments/{$assignment->id}");

        $response->assertForbidden();
    }
}