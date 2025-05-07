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

    public function viewAny(User $user)
    {
        return $user->isInstructor() || $user->isAdmin();
    }

    public function view(User $user, Submission $submission)
    {
        if ($submission->user_id === $user->id) {
            return true;
        }
        return $user->isInstructor() && $user->id === $submission->assignment->course->instructor_id;
    }

    public function create(User $user)
    {
        return $user->isStudent();
    }

    public function update(User $user, Submission $submission)
    {
        // Only instructor can update (e.g., grade)
        return $user->isInstructor() && $user->id === $submission->assignment->course->instructor_id;
    }

    public function grade(User $user, Submission $submission)
    {
        return $this->update($user, $submission);
    }

    public function delete(User $user, Submission $submission)
    {
        return $submission->user_id === $user->id; // allow resubmission
    }
}