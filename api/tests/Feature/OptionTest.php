<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Course;
use App\Models\Assignment;
use App\Models\Question;
use App\Models\Option;
use App\Services\JwtService;
use Symfony\Component\HttpFoundation\Response;

class OptionTest extends TestCase
{
    use RefreshDatabase;

    protected JwtService $jwt;

    protected function setUp(): void
    {
        parent::setUp();
        $this->jwt = $this->app->make(JwtService::class);
    }

    public function test_instructor_can_create_option(): void
    {
        $instructor = User::factory()->create(['role' => 'instructor']);
        $course     = Course::factory()->create(['instructor_id' => $instructor->id]);
        $assignment = Assignment::factory()->create(['course_id' => $course->id]);
        $question   = Question::factory()->create(['assignment_id' => $assignment->id]);

        $payload = [
            'text'       => 'Choice A',
            'order'      => 1,
            'is_correct' => true,
        ];

        $token = $this->jwt->generateToken([
            'sub'  => $instructor->id,
            'role' => $instructor->role,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                         ->postJson("/api/assignments/{$assignment->id}/questions/{$question->id}/options", $payload);

        $response->assertCreated()
                 ->assertJsonPath('data.text', 'Choice A')
                 ->assertJsonPath('data.is_correct', true);

        $this->assertDatabaseHas('options', [
            'question_id' => $question->id,
            'text'        => 'Choice A',
            'is_correct'  => true,
        ]);
    }

    public function test_student_cannot_create_option(): void
    {
        $student    = User::factory()->create(['role' => 'student']);
        $instructor = User::factory()->create(['role' => 'instructor']);
        $course     = Course::factory()->create(['instructor_id' => $instructor->id]);
        $assignment = Assignment::factory()->create(['course_id' => $course->id]);
        $question   = Question::factory()->create(['assignment_id' => $assignment->id]);

        $token = $this->jwt->generateToken([
            'sub'  => $student->id,
            'role' => $student->role,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                         ->postJson("/api/assignments/{$assignment->id}/questions/{$question->id}/options", [
                             'text' => 'Should fail',
                         ]);

        $response->assertForbidden();
    }

    public function test_student_can_list_options(): void
    {
        $instructor = User::factory()->create(['role' => 'instructor']);
        $course     = Course::factory()->create(['instructor_id' => $instructor->id]);
        $assignment = Assignment::factory()->create(['course_id' => $course->id]);
        $question   = Question::factory()->create(['assignment_id' => $assignment->id]);

        Option::factory()->count(3)->create(['question_id' => $question->id]);

        $student = User::factory()->create(['role' => 'student']);
        $student->enrollments()->create([
            'course_id'   => $course->id,
            'enrolled_at' => now(),
            'status'      => 'active',
        ]);

        $token = $this->jwt->generateToken([
            'sub'  => $student->id,
            'role' => $student->role,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                         ->getJson("/api/assignments/{$assignment->id}/questions/{$question->id}/options");

        $response->assertOk()
                 ->assertJsonCount(3, 'data.data');
    }

    public function test_instructor_can_update_option(): void
    {
        $instructor = User::factory()->create(['role'=>'instructor']);
        $course     = Course::factory()->create(['instructor_id'=> $instructor->id]);
        $assignment = Assignment::factory()->create(['course_id'=> $course->id]);
        $question   = Question::factory()->create(['assignment_id'=> $assignment->id]);
        $option     = Option::factory()->create(['question_id'=> $question->id]);

        $token = $this->jwt->generateToken([
            'sub'  => $instructor->id,
            'role' => $instructor->role,
        ]);
        $update = ['text' => 'Updated Choice', 'is_correct' => true];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                         ->patchJson("/api/options/{$option->id}", $update);

        $response->assertOk()
                 ->assertJsonPath('data.text', 'Updated Choice')
                 ->assertJsonPath('data.is_correct', true);

        $this->assertDatabaseHas('options', [
            'id'         => $option->id,
            'text'       => 'Updated Choice',
            'is_correct' => true,
        ]);
    }

    public function test_instructor_can_delete_option(): void
    {
        $instructor = User::factory()->create(['role'=>'instructor']);
        $course     = Course::factory()->create(['instructor_id'=> $instructor->id]);
        $assignment = Assignment::factory()->create(['course_id'=> $course->id]);
        $question   = Question::factory()->create(['assignment_id'=> $assignment->id]);
        $option     = Option::factory()->create(['question_id'=> $question->id]);

        $token = $this->jwt->generateToken([
            'sub'  => $instructor->id,
            'role' => $instructor->role,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                         ->deleteJson("/api/options/{$option->id}");

        $response->assertNoContent();
        $this->assertSoftDeleted('options', ['id' => $option->id]);
    }
}
