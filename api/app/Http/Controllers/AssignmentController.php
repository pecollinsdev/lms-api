<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Assignment;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AssignmentController extends Controller
{
    /**
     * GET /api/courses/{course}/assignments
     */
    public function index(Course $course)
    {
        $this->authorize('viewAny', [Assignment::class, $course]);

        $assignments = $course->assignments()->paginate(15);
        return $this->respond($assignments);
    }

    /**
     * POST /api/courses/{course}/assignments
     */
    public function store(Request $request, Course $course)
    {
        $this->authorize('create', [Assignment::class, $course]);

        $data = $this->validated($request, [
            'title'           => 'required|string|max:255',
            'description'     => 'nullable|string',
            'due_date'        => 'required|date',
            'max_score'       => 'numeric|min:0',
            'submission_type' => 'in:file,essay,quiz',
        ]);

        $assignment = $course->assignments()->create($data);
        return $this->respondCreated($assignment);
    }

    /**
     * GET /api/courses/{course}/assignments/{assignment}
     */
    public function show(Course $course, Assignment $assignment)
    {
        $this->authorize('view', $assignment);
        return $this->respond($assignment);
    }

    /**
     * PUT/PATCH /api/courses/{course}/assignments/{assignment}
     */
    public function update(Request $request, Course $course, Assignment $assignment)
    {
        $this->authorize('update', $assignment);

        $data = $this->validated($request, [
            'title'           => 'sometimes|string|max:255',
            'description'     => 'nullable|string',
            'due_date'        => 'sometimes|date',
            'max_score'       => 'numeric|min:0',
            'submission_type' => 'in:file,essay,quiz',
        ]);

        $assignment->update($data);
        return $this->respond($assignment, 'Assignment updated');
    }

    /**
     * DELETE /api/courses/{course}/assignments/{assignment}
     */
    public function destroy(Course $course, Assignment $assignment)
    {
        $this->authorize('delete', $assignment);

        $assignment->delete();
        return $this->respond(null, 'Assignment deleted', Response::HTTP_NO_CONTENT);
    }
}
