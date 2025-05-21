<?php

namespace App\Policies;

use App\Models\Progress;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProgressPolicy
{
    use HandlesAuthorization;

    public function before(User $user, $ability)
    {
        if ($user->isAdmin()) {
            return true;
        }
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Progress $progress): bool
    {
        return $user->id === $progress->user_id
            || ($user->isInstructor() && $user->id === $progress->moduleItem->module->course->instructor_id);
    }

    public function create(User $user): bool
    {
        return $user->isStudent();
    }

    public function update(User $user, Progress $progress): bool
    {
        return $user->id === $progress->user_id
            || ($user->isInstructor() && $user->id === $progress->moduleItem->module->course->instructor_id);
    }

    public function delete(User $user, Progress $progress): bool
    {
        return $user->isInstructor() && $user->id === $progress->moduleItem->module->course->instructor_id;
    }
} 