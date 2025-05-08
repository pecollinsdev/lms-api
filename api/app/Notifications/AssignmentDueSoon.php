<?php

namespace App\Notifications;

use App\Models\Assignment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AssignmentDueSoon extends Notification
{
    use Queueable;

    protected $assignment;

    /**
     * Create a new notification instance.
     */
    public function __construct(Assignment $assignment)
    {
        $this->assignment = $assignment;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase($notifiable)
    {
        return [
            'title' => 'Assignment Due Soon',
            'message' => "The assignment '{$this->assignment->title}' is due within 24 hours",
            'type' => 'warning',
            'link' => "/courses/{$this->assignment->course_id}/assignments/{$this->assignment->id}",
            'assignment_id' => $this->assignment->id
        ];
    }
}
