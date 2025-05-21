<?php

namespace App\Notifications;

use App\Models\ModuleItem;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ModuleItemDueSoon extends Notification
{
    use Queueable;

    protected $moduleItem;

    /**
     * Create a new notification instance.
     */
    public function __construct(ModuleItem $moduleItem)
    {
        $this->moduleItem = $moduleItem;
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
            'id' => $this->moduleItem->id,
            'type' => 'due_soon',
            'title' => 'Module Item Due Soon',
            'message' => "The module item '{$this->moduleItem->title}' is due within 24 hours",
            'data' => [
                'link' => "/courses/{$this->moduleItem->module->course_id}/module-items/{$this->moduleItem->id}",
                'module_item_id' => $this->moduleItem->id,
            ],
        ];
    }
} 