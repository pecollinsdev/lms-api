<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Module;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ModuleController extends Controller
{
    /**
     * GET /api/courses/{course}/modules
     * List all modules for a course
     */
    public function index(Course $course)
    {
        $this->authorize('viewAny', [Module::class, $course]);

        $modules = $course->modules()
            ->with('moduleItems') // Eager load module items
            ->orderBy('created_at')
            ->paginate(15);

        return $this->respond($modules);
    }

    /**
     * POST /api/courses/{course}/modules
     * Create a new module
     */
    public function store(Request $request, Course $course)
    {
        $this->authorize('create', [Module::class, $course]);

        $data = $this->validated($request, [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $module = $course->modules()->create($data);

        return $this->respondCreated($module);
    }

    /**
     * GET /api/modules/{module}
     * View a single module
     */
    public function show(Module $module)
    {
        $this->authorize('view', $module);

        $module->load('moduleItems'); // Eager load module items
        return $this->respond($module);
    }

    /**
     * PUT/PATCH /api/modules/{module}
     * Update a module
     */
    public function update(Request $request, Module $module)
    {
        $this->authorize('update', $module);

        $data = $this->validated($request, [
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $module->update($data);

        return $this->respond($module, 'Module updated');
    }

    /**
     * DELETE /api/modules/{module}
     * Delete a module
     */
    public function destroy(Module $module)
    {
        $this->authorize('delete', $module);

        $module->delete();

        return $this->respond(null, 'Module deleted', Response::HTTP_NO_CONTENT);
    }

    /**
     * GET /api/modules/{module}/items
     * List all items in a module for the student
     */
    public function items(Module $module)
    {
        $this->authorize('view', $module);

        $userId = request()->user()->id;
        $items = $module->moduleItems()
            ->with([
                'submissions' => function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                },
                'progress' => function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                }
            ])
            ->orderBy('order')
            ->get();

        return response()->json($items);
    }
} 