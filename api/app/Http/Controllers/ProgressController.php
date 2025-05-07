<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Progress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ProgressController extends Controller
{
    /** 
     * GET /api/assignments/{assignment}/progress
     * Instructor: list all students’ progress on this assignment 
     */
    public function index(Assignment $assignment)
    {
        /** @var  \App\Models\User  $user */
        $user = Auth::user();
        if (! $user->isInstructor() || $assignment->course->instructor_id !== $user->id) {
            abort(Response::HTTP_FORBIDDEN);
        }

        $all = Progress::where('assignment_id', $assignment->id)
                       ->with('user')
                       ->paginate(15);

        return $this->respond($all);
    }

    /**
     * GET /api/my-progress
     * Student: list your own progress records
     */
    public function myProgress()
    {
        /** @var  \App\Models\User  $user */
        $user = Auth::user();
        if (! $user->isStudent()) {
            abort(Response::HTTP_FORBIDDEN);
        }

        $mine = Progress::where('user_id', $user->id)
                        ->with('assignment.course')
                        ->paginate(15);

        return $this->respond($mine);
    }

    /**
     * POST /api/assignments/{assignment}/progress
     * Student: create or update your progress on an assignment
     */
    public function store(Request $request, Assignment $assignment)
    {
        /** @var  \App\Models\User  $user */
        $user = Auth::user();
        $enrolled = $user->enrolledCourses()->where('course_id', $assignment->course_id)->exists();
        if (! $user->isStudent() || ! $enrolled) {
            abort(Response::HTTP_FORBIDDEN);
        }

        $data = $request->validate([
            'status' => 'required|in:not_started,in_progress,completed',
        ]) + [
            'user_id'       => $user->id,
            'assignment_id' => $assignment->id,
        ];

        // If they already have a record, update it; otherwise create
        $prog = Progress::updateOrCreate(
            ['user_id' => $user->id, 'assignment_id' => $assignment->id],
            ['status'  => $data['status']]
        );

        return $this->respond($prog, 'Progress saved', Response::HTTP_CREATED);
    }

    /**
     * GET /api/progress/{progress}
     * Owner student or instructor on that assignment can view one record
     */
    public function show(Progress $progress)
    {
        /** @var  \App\Models\User  $user */
        $user = Auth::user();
        $isOwner      = $user->id === $progress->user_id;
        $isInstructor = $user->isInstructor() && $progress->assignment->course->instructor_id === $user->id;

        if (! ($isOwner || $isInstructor)) {
            abort(Response::HTTP_FORBIDDEN);
        }

        return $this->respond($progress);
    }

    /**
     * PATCH /api/progress/{progress}
     * Owner student may update their own progress
     */
    public function update(Request $request, Progress $progress)
    {
        $user = Auth::user();
        if ($user->id !== $progress->user_id) {
            abort(Response::HTTP_FORBIDDEN);
        }

        $data = $request->validate([
            'status' => 'required|in:not_started,in_progress,completed',
        ]);

        $progress->update($data);
        return $this->respond($progress, 'Progress updated');
    }

    /**
     * DELETE /api/progress/{progress}
     * Owner student may delete/reset their record
     */
    public function destroy(Progress $progress)
    {
        $user = Auth::user();
        if ($user->id !== $progress->user_id) {
            abort(Response::HTTP_FORBIDDEN);
        }

        $progress->delete();
        return $this->respond(null, 'Progress removed', Response::HTTP_NO_CONTENT);
    }
}
