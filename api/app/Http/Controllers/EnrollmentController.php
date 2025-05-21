<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
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
        $this->authorize('viewAny', Enrollment::class);
        return $this->respond($user->getEnrolledCourses(true, true));
    }

    /**
     * GET /api/courses/{course}/enrollments
     * List all students enrolled in a course with statistics.
     * Instructor or admin only.
     */
    public function courseStudents(Course $course)
    {
        $this->authorize('viewAny', Enrollment::class);

        // Get enrollments with student relation
        $enrollments = Enrollment::with('student')
            ->where('course_id', $course->id)
            ->paginate(15);

        // Get total items for progress calculation
        $totalItems = $course->moduleItems()->count();

        // Map enrollments to include statistics
        $data = $enrollments->getCollection()->map(function($enrollment) use ($course, $totalItems) {
            $student = $enrollment->student;
            $completedCount = 0;
            foreach ($course->moduleItems as $item) {
                if (in_array($item->type, ['assignment', 'quiz'])) {
                    if ($item->submissions()->where('user_id', $student->id)->where('status', 'graded')->exists()) {
                        $completedCount++;
                    }
                } else {
                    if ($item->progress()->where('user_id', $student->id)->where('status', 'completed')->exists()) {
                        $completedCount++;
                    }
                }
            }
            $progress = $totalItems > 0 ? round($completedCount / $totalItems * 100) : 0;

            // Calculate average grade for the course
            $averageGrade = \App\Models\Grade::where('user_id', $student->id)
                ->whereIn('module_item_id', $course->moduleItems()->pluck('module_items.id'))
                ->where('is_final', true)
                ->avg('score');
            $averageGrade = $averageGrade ? round($averageGrade, 2) : null;

            return [
                'student' => [
                    'id' => $student->id,
                    'name' => $student->name,
                    'email' => $student->email,
                ],
                'enrolled_at' => $enrollment->enrolled_at,
                'progress' => $progress,
                'average_grade' => $averageGrade,
            ];
        });

        // Return paginated response with mapped data
        $result = [
            'data' => $data,
            'meta' => [
                'current_page' => $enrollments->currentPage(),
                'last_page' => $enrollments->lastPage(),
                'per_page' => $enrollments->perPage(),
                'total' => $enrollments->total(),
            ]
        ];

        return response()->json($result);
    }

    /**
     * POST /api/courses/{course}/enroll
     * Enroll the authenticated student in the given course.
     */
    public function store(Request $request, Course $course)
    {
        $user = $request->user();
        $this->authorize('create', Enrollment::class);

        // If instructor is enrolling a student by email
        if ($user->isInstructor() && $request->has('email')) {
            // Verify instructor owns the course
            if ($user->id !== $course->instructor_id) {
                return $this->respond(
                    null,
                    'You are not authorized to enroll students in this course',
                    Response::HTTP_FORBIDDEN
                );
            }

            $enrollment = $course->enrollStudent($user, $request->email);
            if (!$enrollment) {
                return $this->respond(
                    null,
                    'Student is already enrolled',
                    Response::HTTP_CONFLICT
                );
            }

            return $this->respondCreated(
                $enrollment,
                'Student enrolled successfully'
            );
        }

        // Self-enrollment for students
        if ($user->isStudent()) {
            $enrollment = $course->enrollStudent($user);
            if (!$enrollment) {
                return $this->respond(
                    null,
                    'This course is not available for enrollment',
                    Response::HTTP_FORBIDDEN
                );
            }

            return $this->respondCreated(
                $enrollment,
                'Enrolled successfully'
            );
        }

        return $this->respond(
            null,
            'Invalid enrollment request',
            Response::HTTP_BAD_REQUEST
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

        $enrollment = Enrollment::findByUserAndCourse($user->id, $course->id);
        
        if (!$enrollment) {
            return $this->respond(
                null,
                'Enrollment not found',
                Response::HTTP_NOT_FOUND
            );
        }

        // Force-delete so softDeletes don't leave a "deleted_at" trace
        $enrollment->forceDelete();

        return $this->respond(
            null,
            'Unenrolled successfully',
            Response::HTTP_NO_CONTENT
        );
    }
}
