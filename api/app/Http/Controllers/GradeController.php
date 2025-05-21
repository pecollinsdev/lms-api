<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\ModuleItem;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Resources\GradeResource;

class GradeController extends Controller
{
    /**
     * GET /api/module-items/{item}/grades
     * List grades for a specific module item.
     */
    public function index(Request $request, ModuleItem $moduleItem)
    {
        $this->authorize('viewAny', [Grade::class, $moduleItem]);

        $grades = Grade::forModuleItem($moduleItem->id)
            ->with(['student', 'grader'])
            ->final()
            ->paginate(15);

        return GradeResource::collection($grades);
    }

    /**
     * POST /api/module-items/{moduleItem}/grades
     * Create a new grade
     */
    public function store(Request $request, ModuleItem $moduleItem)
    {
        $this->authorize('create', [Grade::class, $moduleItem]);

        $data = $this->validated($request, [
            'user_id' => 'required|exists:users,id',
            'submission_id' => 'nullable|exists:submissions,id',
            'score' => 'required|numeric|min:0|max:' . $moduleItem->max_score,
            'feedback' => 'nullable|string',
            'rubric_scores' => 'nullable|array',
            'is_final' => 'boolean',
        ]);

        $grade = Grade::createGrade($data, $request->user()->id);

        return new GradeResource($grade);
    }

    /**
     * GET /api/grades/{grade}
     * Show a specific grade.
     */
    public function show(Grade $grade)
    {
        $this->authorize('view', $grade);

        return new GradeResource($grade->load(['student', 'grader', 'moduleItem']));
    }

    /**
     * PUT /api/grades/{grade}
     * Update a grade
     */
    public function update(Request $request, Grade $grade)
    {
        $this->authorize('update', $grade);

        $data = $this->validated($request, [
            'score' => 'sometimes|required|numeric|min:0|max:' . $grade->moduleItem->max_score,
            'feedback' => 'nullable|string',
            'rubric_scores' => 'nullable|array',
            'is_final' => 'boolean',
        ]);

        $grade->updateGrade($data);

        return new GradeResource($grade);
    }

    /**
     * DELETE /api/grades/{grade}
     * Delete a grade.
     */
    public function destroy(Grade $grade)
    {
        $this->authorize('delete', $grade);

        $grade->delete();

        return $this->respond(null, 'Grade deleted', Response::HTTP_NO_CONTENT);
    }

    /**
     * GET /api/students/{student}/grades
     * Get grades for a specific student.
     */
    public function studentGrades(Request $request, User $student)
    {
        $this->authorize('viewStudentGrades', [Grade::class, $student]);

        $grades = Grade::forUser($student->id)
            ->with(['moduleItem', 'grader'])
            ->final()
            ->paginate(15);

        return GradeResource::collection($grades);
    }

    /**
     * GET /api/module-items/{moduleItem}/grades/statistics
     * Get grade statistics for a module item
     */
    public function statistics(ModuleItem $moduleItem)
    {
        $this->authorize('view', $moduleItem);

        $stats = Grade::calculateStatistics($moduleItem->id);

        return response()->json($stats);
    }

    /**
     * GET /api/users/{user}/gpa
     * Get GPA for a user
     */
    public function userGPA(User $user)
    {
        $this->authorize('view', $user);

        $gpa = Grade::calculateGPAForUser($user->id);

        return response()->json([
            'user_id' => $user->id,
            'gpa' => $gpa,
        ]);
    }

    /**
     * GET /api/module-items/{moduleItem}/grades
     * List all grades for a module item
     */
    public function moduleItemGrades(ModuleItem $moduleItem)
    {
        $this->authorize('view', $moduleItem);

        $grades = Grade::forModuleItem($moduleItem->id)
            ->with(['student:id,name,email', 'grader:id,name'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return GradeResource::collection($grades);
    }

    /**
     * GET /api/users/{user}/grades
     * List all grades for a user
     */
    public function userGrades(User $user)
    {
        $this->authorize('view', $user);

        $grades = Grade::forUser($user->id)
            ->with(['moduleItem.module.course', 'grader:id,name'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return GradeResource::collection($grades);
    }
} 