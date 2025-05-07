<?php

namespace App\Policies;

use App\Models\Option;
use App\Models\User;
use App\Models\Question;
use Illuminate\Auth\Access\HandlesAuthorization;

class OptionPolicy
{
    use HandlesAuthorization;

    public function before(User $user, $ability)
    {
        if ($user->isAdmin()) {
            return true;
        }
    }

    public function viewAny(User $user, Question $question)
    {
        return (new QuestionPolicy)->view($user, $question);
    }

    public function view(User $user, Option $option)
    {
        return (new QuestionPolicy)->view($user, $option->question);
    }

    public function create(User $user, Question $question)
    {
        return $user->id === $question->assignment->course->instructor_id;
    }

    public function update(User $user, Option $option)
    {
        return $user->id === $option->question->assignment->course->instructor_id;
    }

    public function delete(User $user, Option $option)
    {
        return $user->id === $option->question->assignment->course->instructor_id;
    }
}