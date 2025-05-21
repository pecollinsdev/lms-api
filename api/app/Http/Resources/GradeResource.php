<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GradeResource extends JsonResource
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
            'submission_id' => $this->submission_id,
            'score' => $this->score,
            'letter_grade' => $this->letter_grade,
            'feedback' => $this->feedback,
            'graded_by' => $this->graded_by,
            'graded_at' => $this->graded_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

        // Add submission data if loaded
        if ($this->relationLoaded('submission')) {
            $data['submission'] = new SubmissionResource($this->submission);
        }

        // Add grader data if loaded
        if ($this->relationLoaded('grader')) {
            $data['grader'] = [
                'id' => $this->grader->id,
                'name' => $this->grader->name,
            ];
        }

        return $data;
    }
} 