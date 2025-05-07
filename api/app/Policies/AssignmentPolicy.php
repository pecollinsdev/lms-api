<?php

namespace App\Policies;

use App\Models\Assignment;
use App\Models\User;
use App\Models\Course;
use Illuminate\Auth\Access\HandlesAuthorization;

class AssignmentPolicy
{
    use HandlesAuthorization;

    public function before(User $user, $ability)
    {
        if ($user->isAdmin()) {
            return true;
        }
    }

    public function viewAny(User $user, Course $course)
    {
        return $user->isStudent() && $user->enrolledCourses->contains($course)
            || ($user->isInstructor() && $user->id === $course->instructor_id);
    }

    public function view(User $user, Assignment $assignment)
    {
        $course = $assignment->course;
        return $this->viewAny($user, $course);
    }

    public function create(User $user, Course $course)
    {
        return $user->isInstructor() && $user->id === $course->instructor_id;
    }

    public function update(User $user, Assignment $assignment)
    {
        return $user->id === $assignment->course->instructor_id;
    }

    public function delete(User $user, Assignment $assignment)
    {
        return $user->id === $assignment->course->instructor_id;
    }
}