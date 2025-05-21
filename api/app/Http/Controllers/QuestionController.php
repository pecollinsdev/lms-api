<?php

namespace App\Http\Controllers;

use App\Models\ModuleItem;
use App\Models\Question;
use App\Models\Option;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Resources\QuestionResource;

class QuestionController extends Controller
{
    /**
     * GET /api/module-items/{moduleItem}/questions
     */
    public function index(ModuleItem $moduleItem)
    {
        $this->authorize('viewAny', [Question::class, $moduleItem]);

        $questions = $moduleItem->questions()
            ->with('options')  // Eager load options
            ->paginate(15);
            
        return $this->respond($questions);
    }

    /**
     * POST /api/module-items/{moduleItem}/questions
     * Create a new question
     */
    public function store(Request $request, ModuleItem $moduleItem)
    {
        $this->authorize('create', [Question::class, $moduleItem]);

        $data = $this->validated($request, [
            'type' => 'required|in:multiple_choice,text',
            'prompt' => 'required|string',
            'order' => 'integer|min:0',
            'points' => 'required|numeric|min:0',
            'settings' => 'nullable|array',
        ]);

        // Validate settings based on question type
        if ($data['type'] === 'multiple_choice') {
            $this->validate($request, [
                'settings.allow_multiple' => 'boolean',
                'settings.randomize_options' => 'boolean',
                'settings.show_correct_answer' => 'boolean',
                'options' => 'required|array|min:2',
                'options.*.text' => 'required|string',
                'options.*.is_correct' => 'required|boolean',
            ]);
        }

        $question = $moduleItem->questions()->create($data);

        // Create options for multiple choice questions
        if ($data['type'] === 'multiple_choice' && $request->has('options')) {
            foreach ($request->input('options') as $option) {
                $question->options()->create([
                    'text' => $option['text'],
                    'is_correct' => $option['is_correct'],
                ]);
            }
        }

        return new QuestionResource($question->load('options'));
    }

    /**
     * GET /api/questions/{question}
     */
    public function show(Question $question)
    {
        $this->authorize('view', $question);

        return $this->respond($question);
    }

    /**
     * PUT /api/questions/{question}
     * Update a question
     */
    public function update(Request $request, Question $question)
    {
        $this->authorize('update', $question);

        $data = $this->validated($request, [
            'prompt' => 'sometimes|required|string',
            'order' => 'integer|min:0',
            'points' => 'numeric|min:0',
            'settings' => 'nullable|array',
        ]);

        // Validate settings based on question type
        if ($question->isMultipleChoice()) {
            $this->validate($request, [
                'settings.allow_multiple' => 'boolean',
                'settings.randomize_options' => 'boolean',
                'settings.show_correct_answer' => 'boolean',
            ]);
        }

        $question->update($data);

        return new QuestionResource($question->load('options'));
    }

    /**
     * DELETE /api/questions/{question}
     */
    public function destroy(Question $question)
    {
        $this->authorize('delete', $question);

        $question->forceDelete();
        return $this->respond(null, 'Question permanently deleted', Response::HTTP_NO_CONTENT);
    }

    /**
     * POST /api/questions/{question}/options
     * Add an option to a multiple choice question
     */
    public function addOption(Request $request, Question $question)
    {
        $this->authorize('update', $question);

        if (!$question->isMultipleChoice()) {
            return response()->json([
                'message' => 'Only multiple choice questions can have options'
            ], Response::HTTP_BAD_REQUEST);
        }

        $data = $this->validated($request, [
            'text' => 'required|string',
            'is_correct' => 'required|boolean',
        ]);

        $option = $question->options()->create($data);

        return response()->json($option);
    }

    /**
     * PUT /api/questions/{question}/options/{option}
     * Update an option
     */
    public function updateOption(Request $request, Question $question, Option $option)
    {
        $this->authorize('update', $question);

        if (!$question->isMultipleChoice()) {
            return response()->json([
                'message' => 'Only multiple choice questions can have options'
            ], Response::HTTP_BAD_REQUEST);
        }

        $data = $this->validated($request, [
            'text' => 'sometimes|required|string',
            'is_correct' => 'boolean',
        ]);

        $option->update($data);

        return response()->json($option);
    }

    /**
     * DELETE /api/questions/{question}/options/{option}
     * Delete an option
     */
    public function deleteOption(Question $question, Option $option)
    {
        $this->authorize('update', $question);

        if (!$question->isMultipleChoice()) {
            return response()->json([
                'message' => 'Only multiple choice questions can have options'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Prevent deletion if it's the last option
        if ($question->options()->count() <= 1) {
            return response()->json([
                'message' => 'Cannot delete the last option'
            ], Response::HTTP_BAD_REQUEST);
        }

        $option->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
