<?php

namespace App\Notifications;

use App\Models\Submission;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ModuleItemGraded extends Notification
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
            'title' => 'Module Item Graded',
            'message' => "Your submission for {$this->submission->moduleItem->title} has been graded. Score: {$this->submission->score}/{$this->submission->moduleItem->max_score}",
            'type' => 'success',
            'link' => "/courses/{$this->submission->moduleItem->module->course_id}/module-items/{$this->submission->module_item_id}/submissions/{$this->submission->id}",
            'module_item_id' => $this->submission->module_item_id,
            'submission_id' => $this->submission->id
        ];
    }

    public function toArray($notifiable): array
    {
        return [
            'id' => $this->submission->id,
            'type' => 'graded',
            'title' => 'Module Item Graded',
            'message' => "Your submission for {$this->submission->moduleItem->title} has been graded. Score: {$this->submission->score}/{$this->submission->moduleItem->max_score}",
            'data' => [
                'link' => "/courses/{$this->submission->moduleItem->module->course_id}/module-items/{$this->submission->module_item_id}/submissions/{$this->submission->id}",
                'module_item_id' => $this->submission->module_item_id,
            ],
        ];
    }
} 