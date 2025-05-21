<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ModuleResource extends JsonResource
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
            'description' => $this->description,
            'order' => $this->order,
            'status' => $this->status,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

        // Add module items if loaded
        if ($this->relationLoaded('moduleItems')) {
            $data['module_items'] = ModuleItemResource::collection($this->moduleItems);
        }

        // Add instructor data if loaded
        if ($this->relationLoaded('instructor')) {
            $data['instructor'] = [
                'id' => $this->instructor->id,
                'name' => $this->instructor->name,
                'email' => $this->instructor->email,
            ];
        }

        // Add course data if loaded
        if ($this->relationLoaded('course')) {
            $data['course'] = [
                'id' => $this->course->id,
                'title' => $this->course->title,
                'code' => $this->course->code,
            ];
        }

        return $data;
    }
} 