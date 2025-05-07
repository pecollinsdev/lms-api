<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Global before hook: grant all abilities to admins.
     */
    public function before(User $user, string $ability)
    {
        if ($user->isAdmin()) {
            return true;
        }
    }

    /**
     * Determine whether the user can view any users.
     * Typically only admins can list all users.
     */
    public function viewAny(User $user): bool
    {
        return false; // non-admins cannot list users
    }

    /**
     * Determine whether the user can view a given user.
     * Users can view their own profile.
     */
    public function view(User $user, User $model): bool
    {
        return $user->id === $model->id;
    }

    /**
     * Determine whether the user can create new users.
     * Only admins (handled by before() above) can create.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update a given user.
     * Users can update their own profile.
     */
    public function update(User $user, User $model): bool
    {
        return $user->id === $model->id;
    }

    /**
     * Determine whether the user can delete a given user.
     * Admins can delete any user except themselves.
     */
    public function delete(User $user, User $model): bool
    {
        return $user->id !== $model->id;
    }

    /**
     * Determine whether the user can change another user’s role.
     * Only admins (handled by before()) should be able to.
     */
    public function changeRole(User $user, User $model): bool
    {
        // no extra checks—admins allowed via before()
        return false;
    }
}
