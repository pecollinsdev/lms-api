<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\ModuleItem;
use App\Models\Course;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Resources\ModuleItemResource;

class ModuleItemController extends Controller
{
    /**
     * GET /api/modules/{module}/items
     * List all items in a module
     */
    public function index(Module $module)
    {
        $this->authorize('viewAny', [ModuleItem::class, $module]);

        $items = $module->items()
            ->orderBy('order')
            ->paginate(15);

        // Load submissions for all items if user is a student
        if (request()->user()->isStudent()) {
            $items->getCollection()->transform(function ($item) {
                return $item->loadSubmissionsForUser(request()->user());
            });
        }

        return ModuleItemResource::collection($items);
    }

    /**
     * GET /api/students/upcoming-deadlines
     * Get upcoming deadlines for the authenticated student
     */
    public function upcomingDeadlines(Request $request)
    {
        $this->authorize('viewAny', ModuleItem::class);
        
        $daysAhead = $request->get('days_ahead', 14);
        $deadlines = ModuleItem::getUpcomingDeadlinesForStudent(
            $request->user(),
            (int) $daysAhead
        );

        return response()->json($deadlines);
    }

    /**
     * GET /api/students/calendar
     * Get calendar data for the authenticated student
     */
    public function calendarData(Request $request)
    {
        $this->authorize('viewAny', ModuleItem::class);
        
        $calendarData = ModuleItem::getCalendarDataForStudent($request->user());
        return response()->json($calendarData);
    }

    /**
     * POST /api/courses/{course}/modules/{module}/items
     * Create a new module item
     */
    public function store(Request $request, Course $course, Module $module)
    {
        $this->authorize('create', [ModuleItem::class, $module]);

        $data = $this->validated($request, [
            'type' => 'required|string|in:video,assignment,quiz,document',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'order' => 'integer|min:0',
            'max_score' => 'nullable|numeric|min:0',
            'submission_type' => 'nullable|in:file,essay,quiz',
            'content_data' => 'nullable|array',
            'settings' => 'nullable|array',
        ]);

        // Validate content_data based on type
        $this->validateContentData($request, $data['type']);

        $moduleItem = $module->items()->create($data);

        return new ModuleItemResource($moduleItem);
    }

    /**
     * GET /api/modules/{module}/items/{item}
     * Show a single module item
     */
    public function show(ModuleItem $moduleItem)
    {
        $this->authorize('view', $moduleItem);

        if (request()->user()->isStudent()) {
            $moduleItem->loadSubmissionsForUser(request()->user());
        }

        return new ModuleItemResource($moduleItem);
    }

    /**
     * PUT /api/modules/{module}/items/{item}
     * Update a module item
     */
    public function update(Request $request, ModuleItem $moduleItem)
    {
        $this->authorize('update', $moduleItem);

        $data = $this->validated($request, [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'order' => 'integer|min:0',
            'max_score' => 'nullable|numeric|min:0',
            'submission_type' => 'nullable|in:file,essay,quiz',
            'content_data' => 'nullable|array',
            'settings' => 'nullable|array',
        ]);

        // Validate content_data based on type
        if (isset($data['content_data'])) {
            $this->validateContentData($request, $moduleItem->type);
        }

        $moduleItem->update($data);

        return new ModuleItemResource($moduleItem);
    }

    /**
     * DELETE /api/modules/{module}/items/{item}
     * Delete a module item
     */
    public function destroy(ModuleItem $moduleItem)
    {
        $this->authorize('delete', $moduleItem);

        $moduleItem->delete();

        return $this->respond(null, 'Module item deleted', Response::HTTP_NO_CONTENT);
    }

    /**
     * POST /api/module-items/{moduleItem}/reorder
     * Reorder module items
     */
    public function reorder(Request $request, ModuleItem $moduleItem)
    {
        $this->authorize('update', $moduleItem);

        $data = $this->validated($request, [
            'order' => 'required|integer|min:0',
        ]);

        $moduleItem->update(['order' => $data['order']]);

        return new ModuleItemResource($moduleItem);
    }

    /**
     * GET /api/courses/{course}/submittable-items
     * List all module items that can be submitted (assignments and quizzes)
     */
    public function submittableItems(Course $course)
    {
        $this->authorize('view', $course);

        $items = $course->moduleItems()
            ->submittable()
            ->with(['module', 'questions'])
            ->orderBy('due_date')
            ->paginate(15);

        // Add submission status for each item if user is a student
        if (request()->user()->isStudent()) {
            $items->getCollection()->transform(function ($item) {
                return $item->loadSubmissionsForUser(request()->user());
            });
        }

        return ModuleItemResource::collection($items);
    }

    /**
     * Validate content data based on type
     */
    protected function validateContentData(Request $request, string $type)
    {
        $rules = [];

        switch ($type) {
            case 'video':
                $rules = [
                    'content_data.video_url' => 'required|url',
                    'content_data.video_provider' => 'required|in:youtube,vimeo,custom',
                    'content_data.video_duration' => 'nullable|integer|min:0',
                    'content_data.video_allow_download' => 'nullable|boolean',
                    'settings.auto_complete' => 'nullable|boolean',
                    'settings.required_watch_time' => 'nullable|integer|min:0',
                ];
                break;

            case 'document':
                $rules = [
                    'content_data.document_url' => 'required|url',
                    'content_data.document_type' => 'required|string',
                    'content_data.document_size' => 'nullable|integer|min:0',
                    'content_data.document_allow_download' => 'nullable|boolean',
                    'settings.required_read_time' => 'nullable|integer|min:0',
                ];
                break;

            case 'assignment':
                $rules = [
                    'content_data.assignment_instructions' => 'required|string',
                    'max_score' => 'required|numeric|min:0',
                    'submission_type' => 'required|in:file,essay,quiz',
                    'settings.max_attempts' => 'nullable|integer|min:1',
                    'settings.allow_late_submission' => 'nullable|boolean',
                    'settings.late_submission_penalty' => 'nullable|integer|min:0|max:100',
                    'settings.require_peer_review' => 'nullable|boolean',
                    'settings.peer_review_count' => 'nullable|integer|min:1',
                ];
                break;

            case 'quiz':
                $rules = [
                    'content_data.quiz_instructions' => 'required|string',
                    'content_data.time_limit' => 'nullable|integer|min:0',
                    'content_data.allow_retake' => 'nullable|boolean',
                    'content_data.show_correct_answers' => 'nullable|boolean',
                    'content_data.passing_score' => 'nullable|integer|min:0|max:100',
                    'settings.randomize_questions' => 'nullable|boolean',
                    'settings.show_progress' => 'nullable|boolean',
                    'settings.allow_skip' => 'nullable|boolean',
                ];
                break;
        }

        if (!empty($rules)) {
            $this->validate($request, $rules);
        }
    }
} 