<?php

namespace App\Notifications;

use App\Models\Submission;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AssignmentSubmitted extends Notification
{
    use Queueable;

    protected $submission;

    /**
     * Create a new notification instance.
     */
    public function __construct(Submission $submission)
    {
        $this->submission = $submission;
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
            'title' => 'New Assignment Submission',
            'message' => "A new submission has been received for {$this->submission->assignment->title}",
            'type' => 'info',
            'link' => "/courses/{$this->submission->assignment->course_id}/assignments/{$this->submission->assignment_id}/submissions/{$this->submission->id}",
            'assignment_id' => $this->submission->assignment_id,
            'submission_id' => $this->submission->id
        ];
    }
}
