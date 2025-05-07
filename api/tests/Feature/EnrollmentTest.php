<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Course;
use App\Models\Enrollment;
use App\Services\JwtService;
use Symfony\Component\HttpFoundation\Response;

class EnrollmentTest extends TestCase
{
    use RefreshDatabase;

    protected JwtService $jwt;

    protected function setUp(): void
    {
        parent::setUp();
        $this->jwt = $this->app->make(JwtService::class);
    }

    public function test_student_can_enroll_in_course(): void
    {
        $instructor = User::factory()->create(['role' => 'instructor']);
        $course = Course::factory()->create([
            'instructor_id' => $instructor->id,
            'is_published'  => true,
        ]);

        $student = User::factory()->create(['role' => 'student']);
        $token = $this->jwt->generateToken([
            'sub'  => $student->id,
            'role' => $student->role,
        ]);

        $response = $this
            ->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/courses/{$course->id}/enroll");

        $response->assertCreated()
                 ->assertJsonPath('message', 'Enrolled successfully');

        $this->assertDatabaseHas('enrollments', [
            'user_id'   => $student->id,
            'course_id' => $course->id,
            'status'    => 'active',
        ]);
    }

    public function test_student_cannot_enroll_twice(): void
    {
        $instructor = User::factory()->create(['role' => 'instructor']);
        $course = Course::factory()->create(['instructor_id' => $instructor->id]);

        $student = User::factory()->create(['role' => 'student']);
        $token   = $this->jwt->generateToken([
            'sub'  => $student->id,
            'role' => $student->role,
        ]);

        // First enrollment
        $this->withHeader('Authorization', 'Bearer ' . $token)
             ->postJson("/api/courses/{$course->id}/enroll")
             ->assertCreated();

        // Second (duplicate) enrollment
        $this->withHeader('Authorization', 'Bearer ' . $token)
             ->postJson("/api/courses/{$course->id}/enroll")
             ->assertStatus(Response::HTTP_CONFLICT)
             ->assertJsonPath('message', 'Already enrolled');
    }

    public function test_student_can_unenroll_from_course(): void
    {
        $instructor = User::factory()->create(['role' => 'instructor']);
        $course = Course::factory()->create(['instructor_id' => $instructor->id]);

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
            ->deleteJson("/api/courses/{$course->id}/unenroll");

        $response->assertNoContent();

        $this->assertDatabaseMissing('enrollments', [
            'user_id'   => $student->id,
            'course_id' => $course->id,
        ]);
    }

    public function test_student_can_list_their_courses(): void
    {
        $instructor = User::factory()->create(['role' => 'instructor']);
        $course1 = Course::factory()->create(['instructor_id' => $instructor->id]);
        $course2 = Course::factory()->create(['instructor_id' => $instructor->id]);

        $student = User::factory()->create(['role' => 'student']);
        Enrollment::factory()->create([
            'user_id'   => $student->id,
            'course_id' => $course1->id,
        ]);

        $token = $this->jwt->generateToken([
            'sub'  => $student->id,
            'role' => $student->role,
        ]);

        $response = $this
            ->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/my-courses');

        $response->assertOk()
                 ->assertJsonCount(1, 'data.data')
                 ->assertJsonPath('data.data.0.id', $course1->id);
    }

    public function test_instructor_can_list_students_in_course(): void
    {
        $instructor = User::factory()->create(['role' => 'instructor']);
        $course = Course::factory()->create(['instructor_id' => $instructor->id]);

        $student1 = User::factory()->create(['role' => 'student']);
        $student2 = User::factory()->create(['role' => 'student']);

        Enrollment::factory()->create([
            'user_id'   => $student1->id,
            'course_id' => $course->id,
        ]);
        Enrollment::factory()->create([
            'user_id'   => $student2->id,
            'course_id' => $course->id,
        ]);

        $token = $this->jwt->generateToken([
            'sub'  => $instructor->id,
            'role' => $instructor->role,
        ]);

        $response = $this
            ->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson("/api/courses/{$course->id}/enrollments");

        $response->assertOk()
                 ->assertJsonCount(2, 'data.data')
                 ->assertJsonPath('data.data.0.id', $student1->id)
                 ->assertJsonPath('data.data.1.id', $student2->id);
    }
}
