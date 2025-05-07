<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\ModuleItem;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

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

        return $this->respond($items);
    }

    /**
     * POST /api/modules/{module}/items
     * Create a new module item
     */
    public function store(Request $request, Module $module)
    {
        $this->authorize('create', [ModuleItem::class, $module]);

        $data = $this->validated($request, [
            'type' => 'required|string|in:video,assignment,quiz,document',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'order' => 'integer|min:0',
        ]);

        $item = $module->items()->create($data);

        return $this->respondCreated($item);
    }

    /**
     * GET /api/module-items/{moduleItem}
     * View a single module item
     */
    public function show(ModuleItem $moduleItem)
    {
        $this->authorize('view', $moduleItem);

        // Eager load module and course relationships
        $moduleItem->load(['module.course']);

        return $this->respond($moduleItem);
    }

    /**
     * PUT/PATCH /api/module-items/{moduleItem}
     * Update a module item
     */
    public function update(Request $request, ModuleItem $moduleItem)
    {
        $this->authorize('update', $moduleItem);

        $data = $this->validated($request, [
            'type' => 'sometimes|string|in:video,assignment,quiz,document',
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'order' => 'integer|min:0',
        ]);

        $moduleItem->update($data);

        return $this->respond($moduleItem, 'Module item updated');
    }

    /**
     * DELETE /api/module-items/{moduleItem}
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

        return $this->respond($moduleItem, 'Module item reordered');
    }
} 