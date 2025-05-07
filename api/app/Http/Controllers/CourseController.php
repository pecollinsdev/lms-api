<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

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
                             ->paginate(15);
        } else {
            $courses = Course::published()
                             ->paginate(15);
        }

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
}
