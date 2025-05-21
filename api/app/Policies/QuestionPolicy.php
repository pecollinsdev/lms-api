<?php

namespace App\Policies;

use App\Models\Question;
use App\Models\User;
use App\Models\ModuleItem;
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

    public function viewAny(User $user, ModuleItem $moduleItem)
    {
        return (new ModuleItemPolicy)->view($user, $moduleItem);
    }

    public function view(User $user, Question $question)
    {
        return (new ModuleItemPolicy)->view($user, $question->moduleItem);
    }

    public function create(User $user, ModuleItem $moduleItem)
    {
        return (new ModuleItemPolicy)->update($user, $moduleItem);
    }

    public function update(User $user, Question $question)
    {
        return (new ModuleItemPolicy)->update($user, $question->moduleItem);
    }

    public function delete(User $user, Question $question)
    {
        return (new ModuleItemPolicy)->update($user, $question->moduleItem);
    }
}
