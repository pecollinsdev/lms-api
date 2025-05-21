<?php

namespace App\Http\Controllers;

use App\Models\ModuleItem;
use App\Models\Progress;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Resources\ProgressResource;
use App\Models\Course;
use App\Models\Module;

class ProgressController extends Controller
{
    /** 
     * GET /api/module-items/{item}/progress
     * List all students' progress on this module item (instructor only)
     */
    public function index(ModuleItem $moduleItem)
    {
        $this->authorize('viewAny', Progress::class);
        return ProgressResource::collection(Progress::getForModuleItem($moduleItem->id));
    }

    /**
     * GET /api/progress/my-progress
     * List your own progress records (student only)
     */
    public function myProgress(Request $request)
    {
        $user = $request->user();
        $courses = $user->enrolledCourses()->with('modules.items')->get();
        $results = [];

        foreach ($courses as $course) {
            $moduleItems = $course->moduleItems;
            $completedCount = 0;
            $itemResults = [];
            foreach ($moduleItems as $item) {
                $status = 'not_started';
                if (in_array($item->type, ['assignment', 'quiz'])) {
                    if ($item->submissions()->where('user_id', $user->id)->where('status', 'graded')->exists()) {
                        $status = 'completed';
                        $completedCount++;
                    }
                } else {
                    if ($item->progress()->where('user_id', $user->id)->where('status', 'completed')->exists()) {
                        $status = 'completed';
                        $completedCount++;
                    }
                }
                $itemResults[] = [
                    'module_item_id' => $item->id,
                    'title' => $item->title,
                    'type' => $item->type,
                    'status' => $status,
                ];
            }
            $totalItems = count($moduleItems);
            $progressPercentage = $totalItems > 0 ? round($completedCount / $totalItems * 100) : 0;
            $results[] = [
                'course_id' => $course->id,
                'course_title' => $course->title,
                'progress_percentage' => $progressPercentage,
                'items' => $itemResults
            ];
        }
        return response()->json($results);
    }

    /**
     * POST /api/progress
     * Create or update progress for a module item
     */
    public function store(Request $request)
    {
        $data = $this->validate($request, [
            'module_item_id' => 'required|exists:module_items,id',
            'status' => 'required|in:not_started,in_progress,submitted,graded,completed',
            'progress_data' => 'nullable|array',
        ]);

        $moduleItem = ModuleItem::findOrFail($data['module_item_id']);
        $this->authorize('create', [Progress::class, $moduleItem]);

        // Validate progress data based on module item type
        if ($moduleItem->isVideo()) {
            $this->validate($request, [
                'progress_data.watch_time' => 'required|integer|min:0',
                'progress_data.total_duration' => 'required|integer|min:0',
            ]);

            if (isset($moduleItem->settings['required_watch_time'])) {
                $watchPercentage = ($data['progress_data']['watch_time'] / $data['progress_data']['total_duration']) * 100;
                if ($watchPercentage >= $moduleItem->settings['required_watch_time']) {
                    $data['status'] = 'completed';
                }
            }
        } elseif ($moduleItem->isDocument()) {
            $this->validate($request, [
                'progress_data.read_time' => 'required|integer|min:0',
                'progress_data.total_pages' => 'required|integer|min:0',
            ]);

            if (isset($moduleItem->settings['required_read_time'])) {
                $readPercentage = ($data['progress_data']['read_time'] / $data['progress_data']['total_pages']) * 100;
                if ($readPercentage >= $moduleItem->settings['required_read_time']) {
                    $data['status'] = 'completed';
                }
            }
        }

        $progress = $moduleItem->progress()->updateOrCreate(
            ['user_id' => $request->user()->id],
            array_merge($data, ['last_updated_at' => now()])
        );

        return new ProgressResource($progress);
    }

    /**
     * GET /api/progress/{progress}
     * Show a single progress record (owner or instructor)
     */
    public function show(Progress $progress)
    {
        $this->authorize('view', $progress);

        return new ProgressResource($progress);
    }

    /**
     * PUT /api/progress/{progress}
     * Update your own progress record (owner only)
     */
    public function update(Request $request, Progress $progress)
    {
        $this->authorize('update', $progress);

        $data = $this->validated($request, [
            'status' => 'required|in:not_started,in_progress,submitted,graded',
        ]);

        $progress->update([
            'status' => $data['status'],
            'completed_at' => $data['status'] === 'graded' ? now() : null,
        ]);

        return new ProgressResource($progress);
    }

    /**
     * DELETE /api/progress/{progress}
     * Delete/reset your own progress record (owner only)
     */
    public function destroy(Progress $progress)
    {
        $this->authorize('delete', $progress);

        $progress->delete();
        return $this->respond(null, 'Progress removed', Response::HTTP_NO_CONTENT);
    }

    /**
     * GET /api/courses/{course}/progress
     * Get progress data for all module items in a course
     */
    public function courseProgress(Request $request, Course $course)
    {
        $this->authorize('view', $course);

        $progress = $course->moduleItems()
            ->with(['progress' => function ($query) use ($request) {
                $query->where('user_id', $request->user()->id);
            }])
            ->get()
            ->map(function ($item) {
                return [
                    'module_item_id' => $item->id,
                    'title' => $item->title,
                    'type' => $item->type,
                    'status' => $item->progress->first()?->status ?? 'not_started',
                    'last_updated' => $item->progress->first()?->last_updated_at,
                ];
            });

        return response()->json($progress);
    }

    /**
     * GET /api/modules/{module}/progress
     * Get progress data for all items in a module
     */
    public function moduleProgress(Request $request, Module $module)
    {
        $this->authorize('view', $module);

        $progress = $module->items()
            ->with(['progress' => function ($query) use ($request) {
                $query->where('user_id', $request->user()->id);
            }])
            ->get()
            ->map(function ($item) {
                return [
                    'module_item_id' => $item->id,
                    'title' => $item->title,
                    'type' => $item->type,
                    'status' => $item->progress->first()?->status ?? 'not_started',
                    'last_updated' => $item->progress->first()?->last_updated_at,
                ];
            });

        return response()->json($progress);
    }
}
