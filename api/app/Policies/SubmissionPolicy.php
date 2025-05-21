<?php

namespace App\Policies;

use App\Models\Submission;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SubmissionPolicy
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

    public function view(User $user, Submission $submission): bool
    {
        return $user->id === $submission->user_id
            || ($user->isInstructor() && $user->id === $submission->moduleItem->module->course->instructor_id);
    }

    public function create(User $user): bool
    {
        return $user->isStudent();
    }

    public function update(User $user, Submission $submission): bool
    {
        return $user->isInstructor() && $user->id === $submission->moduleItem->module->course->instructor_id;
    }

    public function grade(User $user, Submission $submission)
    {
        return $this->update($user, $submission);
    }

    public function delete(User $user, Submission $submission): bool
    {
        return $user->isInstructor() && $user->id === $submission->moduleItem->module->course->instructor_id;
    }
}