<?php

namespace App\Policies;

use App\Models\Module;
use App\Models\User;
use App\Models\Course;
use Illuminate\Auth\Access\HandlesAuthorization;

class ModulePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any modules.
     */
    public function viewAny(User $user, Course $course): bool
    {
        // Course instructor can view all modules
        if ($user->isInstructor() && $course->instructor_id === $user->id) {
            return true;
        }

        // Students can view modules if they're enrolled in the course
        if ($user->isStudent()) {
            return $course->students()->where('user_id', $user->id)->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can view the module.
     */
    public function view(User $user, Module $module): bool
    {
        // Course instructor can view any module in their course
        if ($user->isInstructor() && $module->course->instructor_id === $user->id) {
            return true;
        }

        // Students can view modules if they're enrolled in the course
        if ($user->isStudent()) {
            return $module->course->students()->where('user_id', $user->id)->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can create modules.
     */
    public function create(User $user, Course $course): bool
    {
        // Only course instructor can create modules
        return $user->isInstructor() && $course->instructor_id === $user->id;
    }

    /**
     * Determine whether the user can update the module.
     */
    public function update(User $user, Module $module): bool
    {
        // Only course instructor can update modules
        return $user->isInstructor() && $module->course->instructor_id === $user->id;
    }

    /**
     * Determine whether the user can delete the module.
     */
    public function delete(User $user, Module $module): bool
    {
        // Only course instructor can delete modules
        return $user->isInstructor() && $module->course->instructor_id === $user->id;
    }
} 