<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Question;
use App\Models\Answer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AnswerController extends Controller
{
    /**
     * GET /api/assignments/{assignment}/questions/{question}/answers
     * List all answers for a question (instructor only).
     */
    public function index(Assignment $assignment, Question $question)
    {
        /** @var  \App\Models\User  $user */
        $user = Auth::user();

        // only the instructor who owns this assignment's course can list its answers
        if (! $user->isInstructor() || $assignment->course->instructor_id !== $user->id) {
            abort(Response::HTTP_FORBIDDEN);
        }

        $answers = $question
            ->answers()
            ->paginate(15);

        return $this->respond($answers);
    }

    /**
     * POST /api/assignments/{assignment}/questions/{question}/answers
     * Submit an answer (student only).
     */
    public function store(Request $request, Assignment $assignment, Question $question)
    {
        /** @var  \App\Models\User  $user */
        $user = Auth::user();

        // only a student enrolled in the course can submit
        $isEnrolled = $user->enrolledCourses()
                           ->where('course_id', $assignment->course_id)
                           ->exists();

        if (! $user->isStudent() || ! $isEnrolled) {
            abort(Response::HTTP_FORBIDDEN);
        }

        $data = $request->validate([
            'answer_text'        => 'nullable|string',
            'selected_option_id' => 'nullable|exists:options,id',
        ]);

        $answer = Answer::create([
            'user_id'            => $user->id,
            'assignment_id'      => $assignment->id,
            'question_id'        => $question->id,
            'answer_text'        => $data['answer_text'] ?? null,
            'selected_option_id' => $data['selected_option_id'] ?? null,
        ]);

        return $this->respondCreated($answer, 'Answer submitted');
    }

    /**
     * GET /api/answers/{answer}
     * View a single answer (owner or instructor).
     */
    public function show(Answer $answer)
    {
        /** @var  \App\Models\User  $user */
        $user = Auth::user();

        $canView = $user->id === $answer->user_id
            || ($user->isInstructor() && $answer->assignment->instructor_id === $user->id);

        if (! $canView) {
            abort(Response::HTTP_FORBIDDEN);
        }

        return $this->respond($answer);
    }

    /**
     * PATCH /api/answers/{answer}
     * Update an answer (owner only).
     */
    public function update(Request $request, Answer $answer)
    {
        $user = Auth::user();

        if ($user->id !== $answer->user_id) {
            abort(Response::HTTP_FORBIDDEN);
        }

        $data = $request->validate([
            'answer_text'        => 'nullable|string',
            'selected_option_id' => 'nullable|exists:options,id',
        ]);

        $answer->update($data);

        return $this->respond($answer, 'Answer updated');
    }

    /**
     * DELETE /api/answers/{answer}
     * Delete an answer (owner only).
     */
    public function destroy(Answer $answer)
    {
        $user = Auth::user();

        if ($user->id !== $answer->user_id) {
            abort(Response::HTTP_FORBIDDEN);
        }

        $answer->delete();

        return $this->respond(null, 'Answer removed', Response::HTTP_NO_CONTENT);
    }
}
