<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AssignmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $submissions = $this->submissions;
        $graded = $submissions->where('status', 'graded');
        $average_grade = $graded->count() ? round($graded->avg('grade'), 2) : 0.0;

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'due_date' => $this->due_date,
            'max_score' => $this->max_score,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'stats' => [
                'total_submissions' => $submissions->count(),
                'graded_submissions' => $graded->count(),
                'average_grade' => $average_grade,
            ],
            'recent_submissions' => $submissions->sortByDesc('submitted_at')->take(10)->map(function($submission) {
                return [
                    'student' => [
                        'id' => $submission->student->id ?? null,
                        'name' => $submission->student->name ?? 'Unknown Student',
                        'email' => $submission->student->email ?? null,
                        'avatar' => $submission->student->avatar ?? null,
                    ],
                    'submitted_at' => $submission->submitted_at,
                    'status' => $submission->status,
                    'score' => $submission->score,
                    'grade' => $submission->grade,
                ];
            })->values(),
        ];
    }
} 