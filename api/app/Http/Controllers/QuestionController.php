<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Question;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class QuestionController extends Controller
{
    /**
     * GET /api/assignments/{assignment}/questions
     */
    public function index(Assignment $assignment)
    {
        $this->authorize('viewAny', [Question::class, $assignment]);

        $questions = $assignment->questions()->paginate(15);
        return $this->respond($questions);
    }

    /**
     * POST /api/assignments/{assignment}/questions
     */
    public function store(Request $request, Assignment $assignment)
    {
        $this->authorize('create', [Question::class, $assignment]);

        $data = $this->validated($request, [
            'type'       => 'required|in:multiple_choice,text',
            'prompt'     => 'required|string',
            'order'      => 'integer|min:0',
            'points'     => 'numeric|min:0',
            'settings'   => 'nullable|array',
        ]);

        $question = $assignment->questions()->create($data);

        return $this->respondCreated($question);
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
     * PUT/PATCH /api/questions/{question}
     */
    public function update(Request $request, Question $question)
    {
        $this->authorize('update', $question);

        $data = $this->validated($request, [
            'type'       => 'sometimes|in:multiple_choice,text',
            'prompt'     => 'sometimes|string',
            'order'      => 'integer|min:0',
            'points'     => 'numeric|min:0',
            'settings'   => 'nullable|array',
        ]);

        $question->update($data);

        return $this->respond($question, 'Question updated');
    }

    /**
     * DELETE /api/questions/{question}
     */
    public function destroy(Question $question)
    {
        $this->authorize('delete', $question);

        $question->delete();
        return $this->respond(null, 'Question deleted', Response::HTTP_NO_CONTENT);
    }
}
