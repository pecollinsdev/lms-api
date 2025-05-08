<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SubmissionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'grade' => $this->grade,
            'score' => $this->score,
            'feedback' => $this->feedback,
            'submitted_at' => $this->submitted_at,
            'content' => $this->content,
            'file_path' => $this->file_path,
            'answers' => $this->answers,
            'assignment' => [
                'id' => $this->assignment->id,
                'title' => $this->assignment->title,
                'due_date' => $this->assignment->due_date,
                'max_score' => $this->assignment->max_score,
            ],
            'student' => [
                'id' => $this->student->id,
                'name' => $this->student->name,
                'email' => $this->student->email,
                'avatar' => $this->student->avatar ?? null,
            ],
        ];
    }
} 