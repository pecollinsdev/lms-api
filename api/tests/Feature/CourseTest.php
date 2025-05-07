<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Course;
use App\Services\JwtService;

class CourseTest extends TestCase
{
    use RefreshDatabase;

    protected JwtService $jwt;

    protected function setUp(): void
    {
        parent::setUp();

        // Resolve the JWT service
        $this->jwt = $this->app->make(JwtService::class);
    }

    public function test_instructor_can_create_course(): void
    {
        $instructor = User::factory()->create(['role' => 'instructor']);
        $token = $this->jwt->generateToken([
            'sub'  => $instructor->id,
            'role' => $instructor->role,
        ]);

        $payload = [
            'title'        => 'Intro to Testing',
            'slug'         => 'intro-to-testing',
            'description'  => 'A course on writing tests.',
            'start_date'   => now()->toDateString(),
            'end_date'     => now()->addWeek()->toDateString(),
            'is_published' => true,
        ];

        $response = $this
            ->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/courses', $payload);

        $response
            ->assertCreated()
            ->assertJsonPath('data.title', 'Intro to Testing')
            ->assertJsonPath('data.slug', 'intro-to-testing');

        $this->assertDatabaseHas('courses', [
            'slug'          => 'intro-to-testing',
            'instructor_id' => $instructor->id,
        ]);
    }

    public function test_student_cannot_create_course(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $token = $this->jwt->generateToken([
            'sub'  => $student->id,
            'role' => $student->role,
        ]);

        $payload = [
            'title'      => 'Student Course',
            'slug'       => 'student-course',
            'start_date' => now()->toDateString(),
            'end_date'   => now()->addDay()->toDateString(),
        ];

        $response = $this
            ->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/courses', $payload);

        $response->assertForbidden();
    }

    public function test_student_can_list_only_published_courses(): void
    {
        $instructor = User::factory()->create(['role' => 'instructor']);

        // Create one published and one unpublished course
        Course::create([
            'title'         => 'Published Course',
            'slug'          => 'published-course',
            'description'   => '',
            'instructor_id' => $instructor->id,
            'start_date'    => now()->toDateString(),
            'end_date'      => now()->addWeek()->toDateString(),
            'is_published'  => true,
        ]);

        Course::create([
            'title'         => 'Unpublished Course',
            'slug'          => 'unpublished-course',
            'description'   => '',
            'instructor_id' => $instructor->id,
            'start_date'    => now()->toDateString(),
            'end_date'      => now()->addWeek()->toDateString(),
            'is_published'  => false,
        ]);

        $student = User::factory()->create(['role' => 'student']);
        $token   = $this->jwt->generateToken([
            'sub'  => $student->id,
            'role' => $student->role,
        ]);

        $response = $this
            ->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/courses');

        $response
            ->assertOk()
            // Pagination wraps results under data.data
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.slug', 'published-course');
    }
}
