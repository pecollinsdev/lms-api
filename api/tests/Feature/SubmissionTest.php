<?php
// tests/Feature/SubmissionTest.php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Course;
use App\Models\Assignment;
use App\Models\Submission;
use App\Services\JwtService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Question;
use App\Models\Option;

class SubmissionTest extends TestCase
{
    use RefreshDatabase;

    protected JwtService $jwt;

    protected function setUp(): void
    {
        parent::setUp();
        $this->jwt = $this->app->make(JwtService::class);
    }

    public function test_student_can_create_submission()
    {
        $student = User::factory()->student()->create();
        $course = Course::factory()->create();
        $course->students()->attach($student);
        $assignment = Assignment::factory()->for($course)->create(['submission_type' => 'essay']);

        $token = $this->jwt->generateToken(['sub' => $student->id, 'role' => 'student']);
        $response = $this->withHeader('Authorization', 'Bearer '.$token)
                         ->postJson("/api/assignments/{$assignment->id}/submissions", [
                             'submission_type' => 'essay',
                             'content' => 'My essay submission'
                         ]);

        $response->assertCreated()
                 ->assertJsonPath('data.assignment_id', $assignment->id);
        $this->assertDatabaseHas('submissions', [
            'user_id' => $student->id,
            'assignment_id' => $assignment->id,
            'submission_type' => 'essay',
            'content' => 'My essay submission'
        ]);
    }

    public function test_student_cannot_resubmit_same_assignment()
    {
        $student = User::factory()->student()->create();
        $course = Course::factory()->create();
        $course->students()->attach($student);
        $assignment = Assignment::factory()->for($course)->create(['submission_type' => 'essay']);

        // Create first submission
        $token = $this->jwt->generateToken(['sub' => $student->id, 'role' => 'student']);
        $this->withHeader('Authorization', 'Bearer '.$token)
             ->postJson("/api/assignments/{$assignment->id}/submissions", [
                 'submission_type' => 'essay',
                 'content' => 'My first submission'
             ]);

        // Try to submit again
        $response = $this->withHeader('Authorization', 'Bearer '.$token)
                        ->postJson("/api/assignments/{$assignment->id}/submissions", [
                            'submission_type' => 'essay',
                            'content' => 'My second submission'
                        ]);

        $response->assertStatus(Response::HTTP_CONFLICT)
                 ->assertJson(['message' => 'Already submitted']);
    }

    public function test_student_can_list_their_submissions()
    {
        $student = User::factory()->student()->create();
        $course = Course::factory()->create();
        $course->students()->attach($student);
        $assignment = Assignment::factory()->for($course)->create(['submission_type' => 'essay']);

        // Create a submission
        $token = $this->jwt->generateToken(['sub' => $student->id, 'role' => 'student']);
        $this->withHeader('Authorization', 'Bearer '.$token)
             ->postJson("/api/assignments/{$assignment->id}/submissions", [
                 'submission_type' => 'essay',
                 'content' => 'My essay submission'
             ]);

        // List submissions
        $response = $this->withHeader('Authorization', 'Bearer '.$token)
                        ->getJson('/api/my-submissions');

        $response->assertOk()
                 ->assertJsonStructure([
                     'data' => [
                         'data' => [
                             '*' => [
                                 'id',
                                 'assignment_id',
                                 'user_id',
                                 'submission_type',
                                 'content',
                                 'submitted_at',
                                 'status'
                             ]
                         ],
                         'current_page',
                         'per_page',
                         'total'
                     ]
                 ]);
    }

    public function test_instructor_can_list_submissions_for_assignment()
    {
        $instructor = User::factory()->instructor()->create();
        $student = User::factory()->student()->create();
        $course = Course::factory()->create(['instructor_id' => $instructor->id]);
        $course->students()->attach($student);
        $assignment = Assignment::factory()->for($course)->create(['submission_type' => 'essay']);

        // Create a submission
        $token = $this->jwt->generateToken(['sub' => $student->id, 'role' => 'student']);
        $this->withHeader('Authorization', 'Bearer '.$token)
             ->postJson("/api/assignments/{$assignment->id}/submissions", [
                 'submission_type' => 'essay',
                 'content' => 'My essay submission'
             ]);

        // List submissions as instructor
        $token = $this->jwt->generateToken(['sub' => $instructor->id, 'role' => 'instructor']);
        $response = $this->withHeader('Authorization', 'Bearer '.$token)
                         ->getJson("/api/assignments/{$assignment->id}/submissions");

        $response->assertOk()
                 ->assertJsonStructure([
                     'data' => [
                         'data' => [
                             '*' => [
                                 'id',
                                 'user_id',
                                 'assignment_id',
                                 'submission_type',
                                 'content',
                                 'submitted_at',
                                 'status'
                             ]
                         ],
                         'current_page',
                         'per_page',
                         'total'
                     ]
                 ]);
    }

    public function test_instructor_cannot_list_others_assignment_submissions()
    {
        $instructor1 = User::factory()->instructor()->create();
        $instructor2 = User::factory()->instructor()->create();
        $student = User::factory()->student()->create();
        $course = Course::factory()->create(['instructor_id' => $instructor1->id]);
        $course->students()->attach($student);
        $assignment = Assignment::factory()->for($course)->create(['submission_type' => 'essay']);

        // Create a submission
        $token = $this->jwt->generateToken(['sub' => $student->id, 'role' => 'student']);
        $this->withHeader('Authorization', 'Bearer '.$token)
             ->postJson("/api/assignments/{$assignment->id}/submissions", [
                 'submission_type' => 'essay',
                 'content' => 'My essay submission'
             ]);

        // Try to list submissions as another instructor
        $token = $this->jwt->generateToken(['sub' => $instructor2->id, 'role' => 'instructor']);
        $response = $this->withHeader('Authorization', 'Bearer '.$token)
                         ->getJson("/api/assignments/{$assignment->id}/submissions");

        $response->assertForbidden();
    }

    public function test_instructor_can_grade_submission()
    {
        $instructor = User::factory()->instructor()->create();
        $student = User::factory()->student()->create();
        $course = Course::factory()->create(['instructor_id' => $instructor->id]);
        $course->students()->attach($student);
        $assignment = Assignment::factory()->for($course)->create(['submission_type' => 'essay']);

        // Create a submission
        $token = $this->jwt->generateToken(['sub' => $student->id, 'role' => 'student']);
        $response = $this->withHeader('Authorization', 'Bearer '.$token)
             ->postJson("/api/assignments/{$assignment->id}/submissions", [
                 'submission_type' => 'essay',
                 'content' => 'My essay submission'
             ]);
        
        $submission = Submission::first();

        // Grade the submission as instructor
        $token = $this->jwt->generateToken(['sub' => $instructor->id, 'role' => 'instructor']);
        $response = $this->withHeader('Authorization', 'Bearer '.$token)
                        ->patchJson("/api/submissions/{$submission->id}", [
                            'grade' => 85
                        ]);

        $response->assertOk()
                 ->assertJsonStructure([
                     'data' => [
                         'id',
                         'grade',
                         'status'
                     ]
                 ])
                 ->assertJsonPath('data.status', 'graded');

        $responseData = $response->json('data');
        $this->assertEquals(85, (float) $responseData['grade']);

        $this->assertDatabaseHas('submissions', [
            'id' => $submission->id,
            'grade' => 85,
            'status' => 'graded'
        ]);
    }

    public function test_instructor_cannot_grade_others_course_submission()
    {
        $instructor1 = User::factory()->instructor()->create();
        $instructor2 = User::factory()->instructor()->create();
        $student = User::factory()->student()->create();
        $course = Course::factory()->create(['instructor_id' => $instructor1->id]);
        $course->students()->attach($student);
        $assignment = Assignment::factory()->for($course)->create(['submission_type' => 'essay']);

        // Create a submission
        $token = $this->jwt->generateToken(['sub' => $student->id, 'role' => 'student']);
        $response = $this->withHeader('Authorization', 'Bearer '.$token)
             ->postJson("/api/assignments/{$assignment->id}/submissions", [
                 'submission_type' => 'essay',
                 'content' => 'My essay submission'
             ]);
        
        $submission = Submission::first();

        // Try to grade as another instructor
        $token = $this->jwt->generateToken(['sub' => $instructor2->id, 'role' => 'instructor']);
        $response = $this->withHeader('Authorization', 'Bearer '.$token)
                        ->patchJson("/api/submissions/{$submission->id}", [
                            'grade' => 85
                        ]);

        $response->assertForbidden();
    }

    public function test_quiz_submission_is_auto_graded()
    {
        $student = User::factory()->student()->create();
        $course = Course::factory()->create();
        $course->students()->attach($student);
        $assignment = Assignment::factory()->for($course)->create(['submission_type' => 'quiz']);

        // Create questions with options
        $question1 = Question::factory()->for($assignment)->create();
        $question2 = Question::factory()->for($assignment)->create();
        
        // Create options for question 1
        $correctOption1 = Option::factory()->for($question1)->create(['is_correct' => true]);
        $wrongOption1 = Option::factory()->for($question1)->create(['is_correct' => false]);
        
        // Create options for question 2
        $correctOption2 = Option::factory()->for($question2)->create(['is_correct' => true]);
        $wrongOption2 = Option::factory()->for($question2)->create(['is_correct' => false]);

        // Submit quiz with one correct and one wrong answer
        $token = $this->jwt->generateToken(['sub' => $student->id, 'role' => 'student']);
        $response = $this->withHeader('Authorization', 'Bearer '.$token)
                        ->postJson("/api/assignments/{$assignment->id}/submissions", [
                            'submission_type' => 'quiz',
                            'answers' => [
                                $question1->id => $correctOption1->id,  // Correct answer
                                $question2->id => $wrongOption2->id     // Wrong answer
                            ]
                        ]);

        $response->assertCreated()
                 ->assertJsonStructure([
                     'data' => [
                         'id',
                         'grade',
                         'status'
                     ]
                 ]);

        $responseData = $response->json('data');
        $this->assertEquals(50, (float) $responseData['grade']); // 1 out of 2 correct = 50%
        $this->assertEquals('graded', $responseData['status']);

        $this->assertDatabaseHas('submissions', [
            'id' => $responseData['id'],
            'grade' => 50,
            'status' => 'graded'
        ]);
    }
}
