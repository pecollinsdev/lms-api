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

    public function create(User $user)
    {
        return $user->isStudent();
    }

    public function update(User $user, Answer $answer)
    {
        return $answer->submission->user_id === $user->id;
    }

    public function view(User $user, Answer $answer)
    {
        return $this->update($user, $answer)
            || ($user->isInstructor() && $user->id === $answer->question->assignment->course->instructor_id);
    }
}
