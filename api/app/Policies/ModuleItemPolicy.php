<?php

namespace App\Policies;

use App\Models\ModuleItem;
use App\Models\User;
use App\Models\Module;
use Illuminate\Auth\Access\HandlesAuthorization;

class ModuleItemPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any module items.
     */
    public function viewAny(User $user, Module $module): bool
    {
        // Course instructor can view all module items
        if ($user->isInstructor() && $module->course->instructor_id === $user->id) {
            return true;
        }

        // Students can view module items if they're enrolled in the course
        if ($user->isStudent()) {
            return $module->course->students()->where('user_id', $user->id)->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can view the module item.
     */
    public function view(User $user, ModuleItem $moduleItem): bool
    {
        // Course instructor can view any module item in their course
        if ($user->isInstructor() && $moduleItem->module->course->instructor_id === $user->id) {
            return true;
        }

        // Students can view module items if they're enrolled in the course
        if ($user->isStudent()) {
            return $moduleItem->module->course->students()->where('user_id', $user->id)->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can create module items.
     */
    public function create(User $user, Module $module): bool
    {
        // Only course instructor can create module items
        return $user->isInstructor() && $module->course->instructor_id === $user->id;
    }

    /**
     * Determine whether the user can update the module item.
     */
    public function update(User $user, ModuleItem $moduleItem): bool
    {
        // Only course instructor can update module items
        return $user->isInstructor() && $moduleItem->module->course->instructor_id === $user->id;
    }

    /**
     * Determine whether the user can delete the module item.
     */
    public function delete(User $user, ModuleItem $moduleItem): bool
    {
        // Only course instructor can delete module items
        return $user->isInstructor() && $moduleItem->module->course->instructor_id === $user->id;
    }
} 