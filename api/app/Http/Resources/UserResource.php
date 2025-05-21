<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'avatar' => $this->avatar,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

        // Add additional data based on role
        if ($this->role === 'student') {
            // Use pivot data for enrollment information
            if ($this->pivot) {
                $data['enrollment_date'] = $this->pivot->enrolled_at;
                $data['enrollment_status'] = $this->pivot->status;
            }
        } elseif ($this->role === 'instructor') {
            $data['department'] = $this->department;
            $data['title'] = $this->title;
        }

        // Add progress data if loaded
        if ($this->relationLoaded('progress')) {
            $data['progress'] = ProgressResource::collection($this->progress);
        }

        // Add submissions data if loaded
        if ($this->relationLoaded('submissions')) {
            $data['submissions'] = SubmissionResource::collection($this->submissions);
        }

        return $data;
    }
} 