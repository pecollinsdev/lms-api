<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Resources\CourseResource;
use App\Http\Resources\ModuleItemResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Http\Resources\ModuleResource;

class CourseController extends Controller
{
    /**
     * GET /api/courses
     * - Instructors see their own courses
     * - Students see only published courses
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $this->authorize('viewAny', Course::class);
        
        $courses = Course::getCourses([
            'instructor_id' => $user->isInstructor() ? $user->id : null,
            'is_published' => !$user->isInstructor()
        ], true, true);
        
        return CourseResource::collection($courses);
    }

    /**
     * POST /api/courses
     * Create a new course (instructor only)
     */
    public function store(Request $request)
    {
        $this->authorize('create', Course::class);

        $data = $this->validated($request, [
            'title'         => 'required|string|max:255',
            'slug'          => 'required|string|unique:courses,slug',
            'description'   => 'nullable|string',
            'start_date'    => 'required|date',
            'end_date'      => 'required|date|after_or_equal:start_date',
            'is_published'  => 'boolean',
            'cover_image'   => 'nullable|string',
        ]);

        $data['instructor_id'] = $request->user()->id;
        $course = Course::create($data);

        return new CourseResource($course);
    }

    /**
     * GET /api/courses/{course}
     * View a single course
     */
    public function show(Course $course)
    {
        $this->authorize('view', $course);
        
        $course->load(['instructor', 'students' => function($query) {
            $query->withPivot(['enrolled_at', 'status']);
        }, 'modules.moduleItems']);
        
        return new CourseResource($course);
    }

    /**
     * GET /api/courses/{course}/module-items
     * Returns a list of module items for the course
     */
    public function moduleItems(Course $course)
    {
        $this->authorize('view', $course);
        return ModuleItemResource::collection($course->getAllModuleItems());
    }

    /**
     * PUT /api/courses/{course}
     * Update a course (instructor only)
     */
    public function update(Request $request, Course $course)
    {
        $this->authorize('update', $course);

        $data = $this->validated($request, [
            'title'         => 'sometimes|string|max:255',
            'slug'          => 'sometimes|string|unique:courses,slug,' . $course->id,
            'description'   => 'nullable|string',
            'start_date'    => 'sometimes|date',
            'end_date'      => 'sometimes|date|after_or_equal:start_date',
            'is_published'  => 'boolean',
            'cover_image'   => 'nullable|string',
        ]);

        $course->update($data);

        return new CourseResource($course);
    }

    /**
     * DELETE /api/courses/{course}
     * Delete a course (instructor only)
     */
    public function destroy(Course $course)
    {
        $this->authorize('delete', $course);

        $course->forceDelete();

        return $this->respond(null, 'Course deleted', Response::HTTP_NO_CONTENT);
    }

    /**
     * GET /api/courses/{course}/statistics
     * Get course statistics
     */
    public function statistics(Course $course)
    {
        $this->authorize('view', $course);

        $options = [
            'student_count' => true,
            'module_count' => true,
            'item_count' => true,
            'completion' => true,
            'grade' => true,
        ];

        $stats = $course->getStatistics($options);

        return response()->json($stats);
    }

    /**
     * GET /api/courses/{course}/students
     * Get paginated student data with optional completion tracking
     */
    public function students(Course $course)
    {
        $this->authorize('view', $course);

        $withCompleted = request()->boolean('with_completed');
        $perPage = request()->integer('per_page', 15);

        $students = $course->students()
            ->withPivot(['enrolled_at', 'status'])
            ->paginate($perPage);

        return response()->json($students);
    }

    /**
     * POST /api/courses/{course}/enroll
     * Enroll a student in the course
     */
    public function enroll(Request $request, Course $course)
    {
        $this->authorize('enroll', $course);

        $data = $this->validated($request, [
            'student_id' => 'required|exists:users,id',
            'enrolled_by' => 'required|exists:users,id',
        ]);

        $student = User::findOrFail($data['student_id']);
        $enrolledBy = User::findOrFail($data['enrolled_by']);

        $result = $course->handleEnrollment($student, [
            'enrolled_by' => $enrolledBy->id,
            'enrolled_at' => now(),
            'status' => 'active'
        ]);

        if (!$result) {
            return response()->json([
                'message' => 'Failed to enroll student in course'
            ], Response::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'message' => 'Student enrolled successfully',
            'data' => new CourseResource($course)
        ]);
    }

    /**
     * DELETE /api/courses/{course}/unenroll/{student}
     * Unenroll a student from the course
     */
    public function unenroll(Course $course, User $student)
    {
        $this->authorize('unenroll', [$course, $student]);

        $enrollment = $course->enrollments()
            ->where('user_id', $student->id)
            ->first();

        if (!$enrollment) {
            return response()->json([
                'message' => 'Student is not enrolled in this course'
            ], Response::HTTP_NOT_FOUND);
        }

        $enrollment->delete();

        return response()->json([
            'message' => 'Student unenrolled successfully',
            'data' => new CourseResource($course)
        ]);
    }

    /**
     * GET /api/courses/{course}/progress
     * Get progress statistics for a course
     */
    public function progress(Course $course)
    {
        $this->authorize('view', $course);

        $stats = $course->getStatistics([
            'student_count' => true,
            'module_count' => true,
            'item_count' => true,
            'completion' => true,
            'grade' => true
        ]);

        return response()->json($stats);
    }

    /**
     * GET /api/courses/{course}/modules
     * List all modules for a course
     */
    public function modules(Course $course)
    {
        $this->authorize('view', $course);
        
        $modules = $course->modules()
            ->with('moduleItems')
            ->orderBy('created_at')
            ->get();
        
        return ModuleResource::collection($modules);
    }
}
