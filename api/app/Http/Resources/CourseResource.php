<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
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
            'slug' => $this->slug,
            'code' => $this->code,
            'description' => $this->description,
            'credits' => $this->credits,
            'status' => $this->status,
            'is_published' => $this->is_published,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'cover_image' => $this->cover_image,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

        // Add statistics if available
        if ($this->students_count !== null) {
            $data['statistics'] = [
                'student_count' => $this->students_count,
                'module_count' => $this->modules_count,
                'total_items' => $this->total_items,
                'submission_count' => $this->submissions_count,
            ];
        }

        // Add modules if loaded
        if ($this->relationLoaded('modules')) {
            $data['modules'] = ModuleResource::collection($this->modules);
        }

        // Add instructor data if loaded
        if ($this->relationLoaded('instructor')) {
            $data['instructor'] = [
                'id' => $this->instructor->id,
                'name' => $this->instructor->name,
                'email' => $this->instructor->email,
                'role' => $this->instructor->role,
            ];
        }

        // Add enrolled students if loaded
        if ($this->relationLoaded('students')) {
            $data['students'] = $this->students->map(function ($student) {
                $enrollment = $student->pivot;
                return [
                    'id' => $student->id,
                    'name' => $student->name,
                    'email' => $student->email,
                    'role' => $student->role,
                    'avatar' => $student->avatar,
                    'created_at' => $student->created_at,
                    'updated_at' => $student->updated_at,
                    'enrollment_date' => $enrollment ? $enrollment->enrolled_at : null,
                    'enrollment_status' => $enrollment ? $enrollment->status : null
                ];
            });
        }

        return $data;
    }
} 