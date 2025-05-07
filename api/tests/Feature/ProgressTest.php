<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Progress;
use App\Models\User;
use App\Services\JwtService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgressTest extends TestCase
{
    use RefreshDatabase;

    protected JwtService $jwt;

    protected function setUp(): void
    {
        parent::setUp();
        $this->jwt = $this->app->make(JwtService::class);
    }

    public function test_student_can_create_or_update_progress()
    {
        $student    = User::factory()->student()->create();
        $course     = Course::factory()->create(['instructor_id' => User::factory()->instructor()->create()->id]);
        $assignment = Assignment::factory()->for($course)->create();
        $student->enrolledCourses()->attach($course->id);

        $token = $this->jwt->generateToken(['sub'=>$student->id,'role'=>$student->role]);

        $response = $this->withHeader('Authorization','Bearer '.$token)
                         ->postJson("/api/assignments/{$assignment->id}/progress", [
                             'status' => 'in_progress',
                         ]);

        $response->assertCreated()
                 ->assertJsonPath('data.status', 'in_progress');

        $this->assertDatabaseHas('progress', [
            'user_id'       => $student->id,
            'assignment_id' => $assignment->id,
            'status'        => 'in_progress',
        ]);
    }

    public function test_student_cannot_progress_if_not_enrolled()
    {
        $student    = User::factory()->student()->create();
        $assignment = Assignment::factory()->create();

        $token = $this->jwt->generateToken(['sub'=>$student->id,'role'=>$student->role]);

        $this->withHeader('Authorization','Bearer '.$token)
             ->postJson("/api/assignments/{$assignment->id}/progress", ['status'=>'completed'])
             ->assertForbidden();
    }

    public function test_student_can_list_their_progress()
    {
        $student = User::factory()->student()->create();
        Progress::factory()->for($student)->create(['status'=>'completed']);
        Progress::factory()->for($student)->create(['status'=>'in_progress']);

        $token = $this->jwt->generateToken(['sub'=>$student->id,'role'=>$student->role]);

        $this->withHeader('Authorization','Bearer '.$token)
             ->getJson('/api/my-progress')
             ->assertOk()
             ->assertJsonCount(2, 'data.data');
    }

    public function test_instructor_can_list_assignment_progress()
    {
        $instructor = User::factory()->instructor()->create();
        $course     = Course::factory()->create(['instructor_id' => $instructor->id]);
        $assignment = Assignment::factory()->for($course)->create();
        Progress::factory()->create(['assignment_id'=>$assignment->id]);
        Progress::factory()->create(['assignment_id'=>$assignment->id]);

        $token = $this->jwt->generateToken(['sub'=>$instructor->id,'role'=>$instructor->role]);

        $this->withHeader('Authorization','Bearer '.$token)
             ->getJson("/api/assignments/{$assignment->id}/progress")
             ->assertOk()
             ->assertJsonCount(2, 'data.data');
    }

    public function test_instructor_cannot_list_other_assignments_progress()
    {
        $otherInstructor = User::factory()->instructor()->create();
        $course     = Course::factory()->create(['instructor_id' => $otherInstructor->id]);
        $assignment = Assignment::factory()->for($course)->create();

        $me         = User::factory()->instructor()->create();
        $token      = $this->jwt->generateToken(['sub'=>$me->id,'role'=>$me->role]);

        $this->withHeader('Authorization','Bearer '.$token)
             ->getJson("/api/assignments/{$assignment->id}/progress")
             ->assertForbidden();
    }
}
