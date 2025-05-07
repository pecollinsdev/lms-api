<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Course;
use App\Models\Assignment;
use App\Models\Question;
use App\Services\JwtService;
use Symfony\Component\HttpFoundation\Response;

class QuestionTest extends TestCase
{
    use RefreshDatabase;

    protected JwtService $jwt;

    protected function setUp(): void
    {
        parent::setUp();
        $this->jwt = $this->app->make(JwtService::class);
    }

    public function test_instructor_can_create_question(): void
    {
        $instructor = User::factory()->create(['role' => 'instructor']);
        $course     = Course::factory()->create(['instructor_id' => $instructor->id]);
        $assignment = Assignment::factory()->create(['course_id' => $course->id]);

        $payload = [
            'type'     => 'multiple_choice',
            'prompt'   => 'What is 2+2?',
            'order'    => 1,
            'points'   => 5,
        ];

        $token = $this->jwt->generateToken(['sub' => $instructor->id, 'role' => $instructor->role]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
                         ->postJson("/api/assignments/{$assignment->id}/questions", $payload);

        $response->assertCreated()
                 ->assertJsonPath('data.prompt', 'What is 2+2?');

        $this->assertDatabaseHas('questions', [
            'assignment_id' => $assignment->id,
            'prompt'        => 'What is 2+2?'
        ]);
    }

    public function test_student_cannot_create_question(): void
    {
        $student    = User::factory()->create(['role' => 'student']);
        $instructor = User::factory()->create(['role' => 'instructor']);
        $course     = Course::factory()->create(['instructor_id' => $instructor->id]);
        $assignment = Assignment::factory()->create(['course_id' => $course->id]);

        $token = $this->jwt->generateToken(['sub' => $student->id, 'role' => $student->role]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
                         ->postJson("/api/assignments/{$assignment->id}/questions", [
                             'type' => 'text',
                             'prompt' => 'Should fail',
                         ]);

        $response->assertForbidden();
    }

    public function test_student_can_list_questions_for_enrolled_assignment(): void
    {
        $instructor = User::factory()->create(['role' => 'instructor']);
        $course     = Course::factory()->create(['instructor_id' => $instructor->id]);
        $assignment = Assignment::factory()->create(['course_id' => $course->id]);
        $question   = Question::factory()->create(['assignment_id' => $assignment->id]);

        $student = User::factory()->create(['role' => 'student']);
        // enroll student to course so assignment is accessible
        $student->enrollments()->create(['course_id' => $course->id, 'enrolled_at'=>now(), 'status'=>'active']);

        $token = $this->jwt->generateToken(['sub'=>$student->id,'role'=>$student->role]);

        $response = $this->withHeader('Authorization','Bearer '.$token)
                         ->getJson("/api/assignments/{$assignment->id}/questions");

        $response->assertOk()
                 ->assertJsonCount(1,'data.data')
                 ->assertJsonPath('data.data.0.id',$question->id);
    }

    public function test_instructor_can_update_question(): void
    {
        $instructor = User::factory()->create(['role'=>'instructor']);
        $course     = Course::factory()->create(['instructor_id'=>$instructor->id]);
        $assignment = Assignment::factory()->create(['course_id'=>$course->id]);
        $question   = Question::factory()->create(['assignment_id'=>$assignment->id]);

        $token = $this->jwt->generateToken(['sub'=>$instructor->id,'role'=>$instructor->role]);
        $update = ['prompt'=>'Updated prompt'];

        $response = $this->withHeader('Authorization','Bearer '.$token)
                         ->patchJson("/api/questions/{$question->id}", $update);

        $response->assertOk()
                 ->assertJsonPath('data.prompt','Updated prompt');

        $this->assertDatabaseHas('questions',['id'=>$question->id,'prompt'=>'Updated prompt']);
    }

    public function test_instructor_can_delete_question(): void
    {
        $instructor = User::factory()->create(['role'=>'instructor']);
        $course     = Course::factory()->create(['instructor_id'=>$instructor->id]);
        $assignment = Assignment::factory()->create(['course_id'=>$course->id]);
        $question   = Question::factory()->create(['assignment_id'=>$assignment->id]);

        $token = $this->jwt->generateToken(['sub'=>$instructor->id,'role'=>$instructor->role]);

        $response = $this->withHeader('Authorization','Bearer '.$token)
                         ->deleteJson("/api/questions/{$question->id}");

        $response->assertNoContent();
        $this->assertSoftDeleted('questions',['id'=>$question->id]);
    }
}
