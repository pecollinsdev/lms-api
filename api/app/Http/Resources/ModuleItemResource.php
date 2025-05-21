<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ModuleItemResource extends JsonResource
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
            'type' => $this->type,
            'title' => $this->title,
            'description' => $this->description,
            'order' => $this->order,
            'max_score' => $this->max_score,
            'submission_type' => $this->submission_type,
            'due_date' => $this->due_date,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

        // Add module data if loaded
        if ($this->relationLoaded('module')) {
            $data['module'] = [
                'id' => $this->module->id,
                'title' => $this->module->title,
            ];
        }

        // Use the model's content accessor for content
        $data['content'] = $this->content;

        // Add submission data if loaded
        if ($this->relationLoaded('submissions') && $this->submissions->isNotEmpty()) {
            $data['submission'] = new SubmissionResource($this->submissions->first());
        }

        // Add progress data if loaded
        if ($this->relationLoaded('progress')) {
            $data['progress'] = new ProgressResource($this->progress);
        }

        // Add questions if it's a quiz and questions are loaded
        if ($this->isQuiz() && $this->relationLoaded('questions')) {
            $data['questions'] = QuestionResource::collection($this->questions);
        }

        return $data;
    }
} 