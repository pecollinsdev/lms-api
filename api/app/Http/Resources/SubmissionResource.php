<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubmissionResource extends JsonResource
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
            'user_id' => $this->user_id,
            'module_item_id' => $this->module_item_id,
            'content' => $this->content,
            'file_path' => $this->file_path,
            'status' => $this->status,
            'submission_type' => $this->submission_type,
            'answers' => $this->answers,
            'submitted_at' => $this->submitted_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

        // Add user data if loaded
        if ($this->relationLoaded('user')) {
            $data['user'] = [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ];
        }

        // Add module item data if loaded
        if ($this->relationLoaded('moduleItem')) {
            $data['module_item'] = new ModuleItemResource($this->moduleItem);
        }

        // Add grade data if loaded
        if ($this->relationLoaded('grade')) {
            $data['grade'] = new GradeResource($this->grade);
        }

        return $data;
    }
} 