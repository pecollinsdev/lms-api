<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Progress;
use App\Models\Submission;

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

        // authorize listing
        $this->authorize('viewAny', Course::class);

        if ($user->isInstructor()) {
            $courses = Course::where('instructor_id', $user->id)
                            ->with(['instructor', 'students', 'assignments', 'modules.items'])
                            ->paginate(15);
        } else {
            $courses = Course::published()
                            ->with(['instructor', 'students', 'assignments', 'modules.items'])
                            ->paginate(15);
        }

        // Add computed fields to each course
        $courses->getCollection()->transform(function ($course) use ($user) {
            $course->student_count = $course->students->count();
            $course->assignment_count = $course->assignments->count();
            $course->module_count = $course->modules->count();
            $course->total_items = $course->modules->sum(function ($module) {
                return $module->items->count();
            });
            
            // Add enrollment status for students
            if ($user->isStudent()) {
                $course->is_enrolled = $course->students()->where('user_id', $user->id)->exists();
            }

            return $course;
        });

        return $this->respond($courses);
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

        // tie course to the current instructor
        $data['instructor_id'] = $request->user()->id;

        $course = Course::create($data);

        return $this->respondCreated($course);
    }

    /**
     * GET /api/courses/{course}
     * View a single course
     */
    public function show(Course $course)
    {
        $this->authorize('view', $course);

        // Load relationships including modules and their items
        $course->load(['instructor', 'students', 'assignments', 'modules.items']);

        // Add computed fields
        $course->student_count = $course->students->count();
        $course->assignment_count = $course->assignments->count();

        // Optionally, remove assignments from the response if you want to only use modules/module items
        // unset($course->assignments);

        return $this->respond($course);
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

        return $this->respond($course, 'Course updated');
    }

    /**
     * DELETE /api/courses/{course}
     * Delete a course (instructor only)
     */
    public function destroy(Course $course)
    {
        $this->authorize('delete', $course);

        $course->delete();

        return $this->respond(null, 'Course deleted', Response::HTTP_NO_CONTENT);
    }

    /**
     * GET /api/courses/{course}/students
     * Returns a list of students enrolled in the course
     */
    public function students(Course $course)
    {
        $this->authorize('view', $course);

        $students = $course->students()
            ->with(['profile']) // Load any additional student details
            ->paginate(15);

        return $this->respond($students);
    }

    /**
     * GET /api/courses/{course}/assignments
     * Returns a list of assignments for the course
     */
    public function assignments(Course $course)
    {
        $this->authorize('view', $course);

        $assignments = $course->assignments()
            ->with(['submissions']) // Load submissions for total count
            ->paginate(15);

        // Add total submissions count to each assignment
        $assignments->getCollection()->transform(function ($assignment) {
            $assignment->total_submissions = $assignment->submissions->count();
            return $assignment;
        });

        return $this->respond($assignments);
    }

    /**
     * GET /api/courses/{course}/progress
     * Returns progress data for the course
     */
    public function progress(Course $course)
    {
        $this->authorize('view', $course);

        $user = request()->user();
        
        if ($user->isStudent()) {
            // For students, return their own progress
            $progress = Progress::whereIn('assignment_id', $course->assignments->pluck('id'))
                ->where('user_id', $user->id)
                ->with('assignment')
                ->get();
        } else {
            // For instructors, return all students' progress
            $progress = Progress::whereIn('assignment_id', $course->assignments->pluck('id'))
                ->with(['user', 'assignment'])
                ->paginate(15);
        }

        return $this->respond($progress);
    }

    /**
     * GET /api/courses/{course}/statistics
     * Returns aggregate statistics for the course
     */
    public function statistics(Course $course)
    {
        $this->authorize('view', $course);

        // Get basic counts
        $student_count = $course->students()->count();
        $assignment_count = $course->assignments()->count();

        // Calculate average grade
        $submissions = Submission::whereIn('assignment_id', $course->assignments->pluck('id'))
            ->whereNotNull('grade')
            ->get();
        
        $average_grade = $submissions->count() > 0 
            ? round($submissions->avg('grade'), 2) 
            : null;

        // Get completion rates
        $total_possible_submissions = $student_count * $assignment_count;
        $actual_submissions = $submissions->count();
        $completion_rate = $total_possible_submissions > 0 
            ? round(($actual_submissions / $total_possible_submissions) * 100, 2) 
            : 0;

        return $this->respond([
            'student_count' => $student_count,
            'assignment_count' => $assignment_count,
            'average_grade' => $average_grade,
            'completion_rate' => $completion_rate,
            'total_submissions' => $actual_submissions,
        ]);
    }
}
