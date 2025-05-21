<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnswerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'question_id' => $this->question_id,
            'module_item_id' => $this->module_item_id,
            'submission_id' => $this->submission_id,
            'answer_text' => $this->answer_text,
            'selected_option_id' => $this->selected_option_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Relationships
            'user' => new UserResource($this->whenLoaded('user')),
            'question' => new QuestionResource($this->whenLoaded('question')),
            'module_item' => new ModuleItemResource($this->whenLoaded('moduleItem')),
            'submission' => new SubmissionResource($this->whenLoaded('submission')),
            'option' => new OptionResource($this->whenLoaded('option')),
        ];
    }
} 