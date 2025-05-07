<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CoursePolicy
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
        return true; // anyone can list published courses
    }

    public function view(User $user, Course $course)
    {
        if ($course->is_published) {
            return true;
        }
        // allow instructors of the course
        return $user->id === $course->instructor_id;
    }

    public function create(User $user)
    {
        return $user->isInstructor();
    }

    public function update(User $user, Course $course)
    {
        return $user->id === $course->instructor_id;
    }

    public function delete(User $user, Course $course)
    {
        return $user->id === $course->instructor_id;
    }

    public function enroll(User $user, Course $course)
    {
        return $user->isStudent();
    }

    public function unenroll(User $user, Course $course)
    {
        return $user->isStudent();
    }
}
