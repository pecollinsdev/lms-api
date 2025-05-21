<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionResource extends JsonResource
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
            'prompt' => $this->prompt,
            'order' => $this->order,
            'points' => $this->points,
            'settings' => $this->settings,
        ];

        // Add options for multiple choice questions
        if ($this->type === 'multiple_choice') {
            $data['options'] = OptionResource::collection($this->options);
        }

        return $data;
    }
} 