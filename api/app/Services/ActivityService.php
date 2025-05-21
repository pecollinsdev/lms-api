<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\User;

class ActivityService
{
    public function log(User $user, string $type, string $description, $related = null)
    {
        return Activity::create([
            'user_id' => $user->id,
            'type' => $type,
            'description' => $description,
            'related_id' => $related ? $related->id : null,
            'related_type' => $related ? get_class($related) : null
        ]);
    }

    public function logCourseProgress(User $user, $course, $progress)
    {
        return $this->log(
            $user,
            'course_progress',
            "Completed {$progress}% of course: {$course->title}",
            $course
        );
    }

    public function logItemCompletion(User $user, $item)
    {
        return $this->log(
            $user,
            'item_completion',
            "Completed item: {$item->title}",
            $item
        );
    }

    public function logSubmission(User $user, $submission)
    {
        return $this->log(
            $user,
            'submission',
            "Submitted {$submission->moduleItem->title}",
            $submission
        );
    }
} 