<?php

namespace App\Policies;

use App\Models\Grade;
use App\Models\ModuleItem;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class GradePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any grades.
     */
    public function viewAny(User $user, ModuleItem $moduleItem): bool
    {
        // Course instructors can view all grades
        if ($user->id === $moduleItem->module->course->instructor_id) {
            return true;
        }

        // Students can only view their own grades
        if ($user->isStudent()) {
            return $moduleItem->module->course->students()
                ->where('users.id', $user->id)
                ->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can view a specific grade.
     */
    public function view(User $user, Grade $grade): bool
    {
        // Course instructors can view any grade
        if ($user->id === $grade->moduleItem->module->course->instructor_id) {
            return true;
        }

        // Students can only view their own grades
        return $user->isStudent() && $user->id === $grade->user_id;
    }

    /**
     * Determine whether the user can create grades.
     */
    public function create(User $user, ModuleItem $moduleItem): bool
    {
        // Only course instructors can create grades
        return $user->id === $moduleItem->module->course->instructor_id;
    }

    /**
     * Determine whether the user can update a grade.
     */
    public function update(User $user, Grade $grade): bool
    {
        // Only course instructors can update grades
        return $user->id === $grade->moduleItem->module->course->instructor_id;
    }

    /**
     * Determine whether the user can delete a grade.
     */
    public function delete(User $user, Grade $grade): bool
    {
        // Only course instructors can delete grades
        return $user->id === $grade->moduleItem->module->course->instructor_id;
    }

    /**
     * Determine whether the user can view a student's grades.
     */
    public function viewStudentGrades(User $user, User $student): bool
    {
        // Course instructors can view any student's grades
        if ($user->isInstructor()) {
            return $user->courses()
                ->whereHas('students', function ($query) use ($student) {
                    $query->where('users.id', $student->id);
                })
                ->exists();
        }

        // Students can only view their own grades
        return $user->isStudent() && $user->id === $student->id;
    }

    /**
     * Determine whether the user can view grade statistics.
     */
    public function viewStatistics(User $user, ModuleItem $moduleItem): bool
    {
        // Only course instructors can view grade statistics
        return $user->id === $moduleItem->module->course->instructor_id;
    }
} 