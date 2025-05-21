<?php

namespace App\Policies;

use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
class EnrollmentPolicy
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
        // Allow students to self-enroll or instructors to enroll students in their courses
        return $user->isStudent() || $user->isInstructor();
    }

    public function delete(User $user, Enrollment $enrollment)
    {
        // students can unenroll themselves
        if ($user->isStudent()) {
            return $user->id === $enrollment->user_id;
        }
        // instructors can remove students from their course
        return $user->isInstructor() && $user->id === $enrollment->course->instructor_id;
    }

    public function viewAny(User $user)
    {
        // students should be able to see their own courses,
        // instructors/admins can see all enrollments too
        return $user->isStudent()
            || $user->isInstructor()
            || $user->isAdmin();
    }

    public function view(User $user, Enrollment $enrollment)
    {
        return $user->id === $enrollment->user_id
            || ($user->isInstructor() && $user->id === $enrollment->course->instructor_id);
    }
}