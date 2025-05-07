<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnrollmentController extends Controller
{
    /**
     * GET /api/my-courses
     * List all courses the authenticated user is enrolled in.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Only students or admins really need to list their enrollments
        $this->authorize('viewAny', Enrollment::class);

        $courses = $user->enrolledCourses()
                        ->paginate(15);

        return $this->respond($courses);
    }

    /**
     * GET /api/courses/{course}/enrollments
     * List all students enrolled in a course.
     * Instructor or admin only.
     */
    public function courseStudents(Course $course)
    {
        $this->authorize('viewAny', Enrollment::class);

        $students = $course->students()
                           ->paginate(15);

        return $this->respond($students);
    }

    /**
     * POST /api/courses/{course}/enroll
     * Enroll the authenticated student in the given course.
     */
    public function store(Request $request, Course $course)
    {
        $user = $request->user();

        $this->authorize('enroll', $course);

        // Prevent duplicate enrollment
        if ($course->students()->where('user_id', $user->id)->exists()) {
            return $this->respond(
                null,
                'Already enrolled',
                Response::HTTP_CONFLICT
            );
        }

        $enrollment = Enrollment::create([
            'user_id'     => $user->id,
            'course_id'   => $course->id,
            'enrolled_at' => now(),
            'status'      => 'active',
        ]);

        return $this->respondCreated(
            $enrollment,
            'Enrolled successfully'
        );
    }

    /**
     * DELETE /api/courses/{course}/unenroll
     * Unenroll the authenticated student from the course.
     */
    public function destroy(Request $request, Course $course)
    {
        $user = $request->user();

        $this->authorize('unenroll', $course);

        $enrollment = Enrollment::where([
            'user_id'   => $user->id,
            'course_id' => $course->id,
        ])->firstOrFail();


        // Force-delete so softDeletes don’t leave a “deleted_at” trace
        $enrollment->forceDelete();

        return $this->respond(
            null,
            'Unenrolled successfully',
            Response::HTTP_NO_CONTENT
        );
    }
}
