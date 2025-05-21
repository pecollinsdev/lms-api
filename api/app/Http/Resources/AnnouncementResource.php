<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnnouncementResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'type' => $this->type,
            'priority' => $this->priority,
            'status' => $this->status,
            'publish_at' => $this->publish_at,
            'expires_at' => $this->expires_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

        // Add course data if loaded
        if ($this->relationLoaded('course')) {
            $data['course'] = [
                'id' => $this->course->id,
                'title' => $this->course->title,
                'code' => $this->course->code,
            ];
        }

        // Add module data if loaded
        if ($this->relationLoaded('module')) {
            $data['module'] = [
                'id' => $this->module->id,
                'title' => $this->module->title,
            ];
        }

        // Add author data if loaded
        if ($this->relationLoaded('author')) {
            $data['author'] = [
                'id' => $this->author->id,
                'name' => $this->author->name,
                'email' => $this->author->email,
            ];
        }

        // Add attachments if loaded
        if ($this->relationLoaded('attachments')) {
            $data['attachments'] = $this->attachments->map(function ($attachment) {
                return [
                    'id' => $attachment->id,
                    'name' => $attachment->name,
                    'path' => $attachment->path,
                    'type' => $attachment->type,
                    'size' => $attachment->size,
                ];
            });
        }

        return $data;
    }
} 