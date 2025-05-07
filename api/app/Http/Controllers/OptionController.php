<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Question;
use App\Models\Option;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OptionController extends Controller
{
    /**
     * GET /api/assignments/{assignment}/questions/{question}/options
     */
    public function index(Assignment $assignment, Question $question)
    {
        $this->authorize('viewAny', [Option::class, $question]);

        $options = $question->options()
            ->orderBy('order')
            ->paginate(15);

        return $this->respond($options);
    }

    /**
     * POST /api/assignments/{assignment}/questions/{question}/options
     */
    public function store(Request $request, Assignment $assignment, Question $question)
    {
        $this->authorize('create', [Option::class, $question]);

        $data = $this->validated($request, [
            'text'       => 'required|string',
            'order'      => 'sometimes|integer|min:0',
            'is_correct' => 'boolean',
        ]);

        $option = $question->options()->create($data);

        return $this->respondCreated($option);
    }

    /**
     * GET /api/options/{option}
     */
    public function show(Option $option)
    {
        $this->authorize('view', $option);
        return $this->respond($option);
    }

    /**
     * PUT/PATCH /api/options/{option}
     */
    public function update(Request $request, Option $option)
    {
        $this->authorize('update', $option);

        $data = $this->validated($request, [
            'text'       => 'sometimes|string',
            'order'      => 'sometimes|integer|min:0',
            'is_correct' => 'boolean',
        ]);

        $option->update($data);

        return $this->respond($option, 'Option updated');
    }

    /**
     * DELETE /api/options/{option}
     */
    public function destroy(Option $option)
    {
        $this->authorize('delete', $option);
        $option->delete();
        return $this->respond(null, 'Option deleted', Response::HTTP_NO_CONTENT);
    }
}
