<?php

namespace App\Policies;

use App\Models\Answer;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
class AnswerPolicy
{
    use HandlesAuthorization;

    public function before(User $user, $ability)
    {
        if ($user->isAdmin()) {
            return true;
        }
    }

    public function create(User $user): bool
    {
        return $user->isStudent();
    }

    public function update(User $user, Answer $answer): bool
    {
        return $user->id === $answer->user_id
            || ($user->isInstructor() && $user->id === $answer->question->moduleItem->module->course->instructor_id);
    }

    public function view(User $user, Answer $answer): bool
    {
        return $user->id === $answer->user_id
            || ($user->isInstructor() && $user->id === $answer->question->moduleItem->module->course->instructor_id);
    }

    public function delete(User $user, Answer $answer): bool
    {
        return $user->id === $answer->user_id
            || ($user->isInstructor() && $user->id === $answer->question->moduleItem->module->course->instructor_id);
    }
}
