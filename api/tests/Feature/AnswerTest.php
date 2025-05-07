<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Course;
use App\Models\Assignment;
use App\Models\Question;
use App\Models\Option;
use App\Models\Answer;
use App\Services\JwtService;
use Symfony\Component\HttpFoundation\Response;

class AnswerTest extends TestCase
{
    use RefreshDatabase;

    protected JwtService $jwt;

    protected function setUp(): void
    {
        parent::setUp();
        $this->jwt = $this->app->make(JwtService::class);
    }

    public function test_student_can_submit_answer(): void
    {
        $student    = User::factory()->create(['role'=>'student']);
        $instructor = User::factory()->create(['role'=>'instructor']);
        $course     = Course::factory()->create(['instructor_id'=>$instructor->id]);
        $assignment = Assignment::factory()->create(['course_id'=>$course->id]);
        $question   = Question::factory()->create(['assignment_id'=>$assignment->id]);
        $student->enrollments()->create(['course_id'=>$course->id,'enrolled_at'=>now(),'status'=>'active']);

        $payload = ['answer_text'=>'My answer'];
        $token   = $this->jwt->generateToken(['sub'=>$student->id,'role'=>$student->role]);

        $response = $this->withHeader('Authorization','Bearer '.$token)
                         ->postJson("/api/assignments/{$assignment->id}/questions/{$question->id}/answers", $payload);

        $response->assertCreated()
                 ->assertJsonPath('data.answer_text','My answer');

        $this->assertDatabaseHas('answers',['user_id'=>$student->id,'question_id'=>$question->id]);
    }

    public function test_instructor_cannot_submit_answer(): void
    {
        $instructor = User::factory()->create(['role'=>'instructor']);
        $course     = Course::factory()->create(['instructor_id'=>$instructor->id]);
        $assignment = Assignment::factory()->create(['course_id'=>$course->id]);
        $question   = Question::factory()->create(['assignment_id'=>$assignment->id]);

        $token = $this->jwt->generateToken(['sub'=>$instructor->id,'role'=>$instructor->role]);
        $response = $this->withHeader('Authorization','Bearer '.$token)
                         ->postJson("/api/assignments/{$assignment->id}/questions/{$question->id}/answers", ['answer_text'=>'x']);

        $response->assertForbidden();
    }

    public function test_instructor_can_list_answers(): void
    {
        $student    = User::factory()->create(['role'=>'student']);
        $instructor = User::factory()->create(['role'=>'instructor']);
        $course     = Course::factory()->create(['instructor_id'=>$instructor->id]);
        $assignment = Assignment::factory()->create(['course_id'=>$course->id]);
        $question   = Question::factory()->create(['assignment_id'=>$assignment->id]);
        Answer::factory()->count(2)->create([
            'question_id' => $question->id,
            'assignment_id' => $assignment->id,
            'user_id' => $student->id
        ]);

        $token = $this->jwt->generateToken(['sub'=>$instructor->id,'role'=>$instructor->role]);

        $response = $this->withHeader('Authorization','Bearer '.$token)
                         ->getJson("/api/assignments/{$assignment->id}/questions/{$question->id}/answers");

        $response->assertOk()
                 ->assertJsonCount(2,'data.data');
    }

    public function test_owner_can_view_update_delete_their_answer(): void
    {
        $student    = User::factory()->create(['role'=>'student']);
        $instructor = User::factory()->create(['role'=>'instructor']);
        $course     = Course::factory()->create(['instructor_id'=>$instructor->id]);
        $assignment = Assignment::factory()->create(['course_id'=>$course->id]);
        $question   = Question::factory()->create(['assignment_id'=>$assignment->id]);
        $student->enrollments()->create(['course_id'=>$course->id,'enrolled_at'=>now(),'status'=>'active']);
        $answer     = Answer::factory()->create(['user_id'=>$student->id,'question_id'=>$question->id,'assignment_id'=>$assignment->id]);

        $token = $this->jwt->generateToken(['sub'=>$student->id,'role'=>$student->role]);

        // view
        $this->withHeader('Authorization','Bearer '.$token)
             ->getJson("/api/answers/{$answer->id}")
             ->assertOk();

        // update
        $this->withHeader('Authorization','Bearer '.$token)
             ->patchJson("/api/answers/{$answer->id}", ['answer_text'=>'Edited'])
             ->assertOk()
             ->assertJsonPath('data.answer_text','Edited');

        // delete
        $this->withHeader('Authorization','Bearer '.$token)
             ->deleteJson("/api/answers/{$answer->id}")
             ->assertNoContent();

        $this->assertSoftDeleted('answers',['id'=>$answer->id]);
    }
}
