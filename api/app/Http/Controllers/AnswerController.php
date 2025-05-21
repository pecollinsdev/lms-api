<?php

namespace App\Http\Controllers;

use App\Models\ModuleItem;
use App\Models\Question;
use App\Models\Answer;
use App\Models\Submission;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Resources\AnswerResource;

class AnswerController extends Controller
{
    /**
     * GET /api/assignments/{assignment}/questions/{question}/answers
     * List all answers for a question (instructor only).
     */
    public function index(ModuleItem $moduleItem, Question $question)
    {
        $this->authorize('viewAny', Answer::class);

        $answers = $question
            ->answers()
            ->paginate(15);

        return $this->respond($answers);
    }

    /**
     * POST /api/module-items/{moduleItem}/questions/{question}/answers
     * Submit an answer to a question
     */
    public function store(Request $request, ModuleItem $moduleItem, Question $question)
    {
        $this->authorize('create', [Answer::class, $moduleItem]);

        // Validate based on question type
        if ($question->isMultipleChoice()) {
            $data = $this->validated($request, [
                'selected_option_id' => 'required|exists:options,id',
                'submission_id' => 'required|exists:submissions,id',
            ]);

            // Verify the option belongs to this question
            if (!$question->options()->where('id', $data['selected_option_id'])->exists()) {
                return response()->json([
                    'message' => 'Invalid option for this question'
                ], Response::HTTP_BAD_REQUEST);
            }
        } else {
            $data = $this->validated($request, [
                'answer_text' => 'required|string',
                'submission_id' => 'required|exists:submissions,id',
            ]);
        }

        // Check if user has already answered this question in this submission
        $existingAnswer = Answer::where([
            'user_id' => $request->user()->id,
            'question_id' => $question->id,
            'submission_id' => $data['submission_id'],
        ])->first();

        if ($existingAnswer) {
            return response()->json([
                'message' => 'You have already answered this question'
            ], Response::HTTP_CONFLICT);
        }

        // Create the answer
        $answer = $question->answers()->create([
            'user_id' => $request->user()->id,
            'module_item_id' => $moduleItem->id,
            'submission_id' => $data['submission_id'],
            'answer_text' => $data['answer_text'] ?? null,
            'selected_option_id' => $data['selected_option_id'] ?? null,
        ]);

        return new AnswerResource($answer);
    }

    /**
     * GET /api/answers/{answer}
     * View a single answer (owner or instructor).
     */
    public function show(Answer $answer)
    {
        $this->authorize('view', $answer);

        return $this->respond($answer);
    }

    /**
     * PUT /api/answers/{answer}
     * Update an answer
     */
    public function update(Request $request, Answer $answer)
    {
        $this->authorize('update', $answer);

        // Validate based on question type
        if ($answer->question->isMultipleChoice()) {
            $data = $this->validated($request, [
                'selected_option_id' => 'required|exists:options,id',
            ]);

            // Verify the option belongs to this question
            if (!$answer->question->options()->where('id', $data['selected_option_id'])->exists()) {
                return response()->json([
                    'message' => 'Invalid option for this question'
                ], Response::HTTP_BAD_REQUEST);
            }
        } else {
            $data = $this->validated($request, [
                'answer_text' => 'required|string',
            ]);
        }

        $answer->update($data);

        return new AnswerResource($answer);
    }

    /**
     * DELETE /api/answers/{answer}
     * Delete an answer (owner only).
     */
    public function destroy(Answer $answer)
    {
        $this->authorize('delete', $answer);

        $answer->delete();

        return $this->respond(null, 'Answer removed', Response::HTTP_NO_CONTENT);
    }

    /**
     * GET /api/module-items/{moduleItem}/questions/{question}/answers
     * List all answers for a question (instructor only)
     */
    public function questionAnswers(ModuleItem $moduleItem, Question $question)
    {
        $this->authorize('viewAny', [Answer::class, $moduleItem]);

        $answers = $question->answers()
            ->with(['user:id,name,email', 'option'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return AnswerResource::collection($answers);
    }

    /**
     * GET /api/submissions/{submission}/answers
     * List all answers for a submission
     */
    public function submissionAnswers(Submission $submission)
    {
        $this->authorize('view', $submission);

        $answers = $submission->answers()
            ->with(['question', 'option'])
            ->orderBy('created_at')
            ->get();

        return AnswerResource::collection($answers);
    }
}
