<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use App\Models\Assignment;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SubmissionController extends Controller
{
    /**
     * GET /api/assignments/{assignment}/submissions
     * List submissions for an assignment (instructor only).
     */
    public function index(Assignment $assignment)
    {
        /** @var  \App\Models\User  $user */
        $user = Auth::user();

        if (! $user->isInstructor() || $assignment->course->instructor_id !== $user->id) {
            abort(Response::HTTP_FORBIDDEN);
        }

        $submissions = $assignment->submissions()->paginate(15);

        return $this->respond($submissions);
    }

    /**
     * POST /api/assignments/{assignment}/submissions
     * Student creates a submission (file, essay, or quiz). Quizzes are auto-graded.
     */
    public function store(Request $request, Assignment $assignment)
    {
        /** @var  \App\Models\User  $user */
        $user = Auth::user();
        if (! $user->isStudent()) {
            abort(Response::HTTP_FORBIDDEN);
        }

        $enrolled = $user->enrolledCourses()
                         ->where('course_id', $assignment->course_id)
                         ->exists();
        if (! $enrolled) {
            abort(Response::HTTP_FORBIDDEN);
        }

        $already = Submission::where('user_id', $user->id)
            ->where('assignment_id', $assignment->id)
            ->exists();
        if ($already) {
            return response()->json(
                ['message' => 'Already submitted'],
                Response::HTTP_CONFLICT
            );
        }

        $data = $request->validate([
            'submission_type' => 'required|in:file,essay,quiz',
            'content'         => 'required_if:submission_type,essay|string',
            'file_path'       => 'required_if:submission_type,file|string',
            'answers'         => 'required_if:submission_type,quiz|array',
        ]);

        $submission = Submission::create([
            'user_id'         => $user->id,
            'assignment_id'   => $assignment->id,
            'submission_type'=> $data['submission_type'],
            'content'         => $data['content'] ?? null,
            'file_path'       => $data['file_path'] ?? null,
            'answers'         => $data['answers'] ?? null,
            'submitted_at'    => now(),
            'status'          => 'pending',
        ]);

        // auto-grade quizzes immediately
        if ($submission->submission_type === 'quiz') {
            $this->autoGradeQuiz($submission);
            $submission->refresh();
            return $this->respondCreated($submission, 'Quiz auto-graded');
        }

        return $this->respondCreated($submission, 'Submission created');
    }

    /**
     * GET /api/my-submissions
     * List current student's own submissions.
     */
    public function mySubmissions()
    {
        /** @var  \App\Models\User  $user */
        $user = Auth::user();
        if (! $user->isStudent()) {
            abort(Response::HTTP_FORBIDDEN);
        }

        $subs = $user->submissions()->paginate(15);
        return $this->respond($subs);
    }

    /**
     * GET /api/assignments/{assignment}/submissions/{submission}
     * Show a single submission (owner or instructor).
     */
    public function show(Assignment $assignment, Submission $submission)
    {
        /** @var  \App\Models\User  $user */
        $user = Auth::user();

        // students only see their own
        if ($user->isStudent() && $submission->user_id !== $user->id) {
            abort(Response::HTTP_FORBIDDEN);
        }

        // instructors only see submissions in their course
        if ($user->isInstructor() && $assignment->course->instructor_id !== $user->id) {
            abort(Response::HTTP_FORBIDDEN);
        }

        return $this->respond($submission);
    }

    /**
     * PATCH /api/submissions/{submission}
     * Grade or re-grade a submission (instructor only).
     */
    public function update(Request $request, Submission $submission)
    {
        /** @var  \App\Models\User  $user */
        $user = Auth::user();
        $assignment = $submission->assignment;

        if (! $user->isInstructor() || $assignment->course->instructor_id !== $user->id) {
            abort(Response::HTTP_FORBIDDEN);
        }

        $data = $request->validate([
            'grade' => 'required|numeric|min:0|max:100',
        ]);

        $submission->update([
            'grade'  => $data['grade'],
            'status' => 'graded',
        ]);

        return $this->respond($submission, 'Submission graded');
    }

    /**
     * Helper: auto-grade a quiz submission by comparing each answer
     * to the question’s correct option and computing a percentage.
     */
    protected function autoGradeQuiz(Submission $submission)
    {
        $answers = $submission->answers; // [ question_id => selected_option_id, … ]
        $total   = count($answers);
        $correct = 0;

        foreach ($answers as $questionId => $selectedOptionId) {
            $correctOptionId = Question::find($questionId)
                ->options()
                ->where('is_correct', true)
                ->value('id');

            if ($correctOptionId && $correctOptionId == $selectedOptionId) {
                $correct++;
            }
        }

        $percent = $total
            ? round(($correct / $total) * 100, 2)
            : 0.0;

        $submission->update([
            'grade'  => $percent,
            'status' => 'graded',
        ]);
    }
}
