<?php

namespace App\Policies;

use App\Models\Question;
use App\Models\User;
use App\Models\Assignment;
use Illuminate\Auth\Access\HandlesAuthorization;

class QuestionPolicy
{
    use HandlesAuthorization;

    public function before(User $user, $ability)
    {
        if ($user->isAdmin()) {
            return true;
        }
    }

    public function viewAny(User $user, Assignment $assignment)
    {
        return (new AssignmentPolicy)->view($user, $assignment);
    }

    public function view(User $user, Question $question)
    {
        return (new AssignmentPolicy)->view($user, $question->assignment);
    }

    public function create(User $user, Assignment $assignment)
    {
        return $user->id === $assignment->course->instructor_id;
    }

    public function update(User $user, Question $question)
    {
        return $user->id === $question->assignment->course->instructor_id;
    }

    public function delete(User $user, Question $question)
    {
        return $user->id === $question->assignment->course->instructor_id;
    }
}
