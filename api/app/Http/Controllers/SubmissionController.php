<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\ModuleItem;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Resources\SubmissionResource;
use App\Notifications\ModuleItemSubmitted;
use App\Notifications\ModuleItemGraded;
use App\Models\Progress;

class SubmissionController extends Controller
{
    /**
     * GET /api/courses/{course}/module-items/{item}/submissions
     * List submissions for a module item (instructor only).
     */
    public function index(Course $course, ModuleItem $moduleItem)
    {
        $this->authorize('viewAny', Submission::class);

        $submissions = Submission::forModuleItem($moduleItem->id)
            ->with(['user', 'submissionAnswers'])
            ->paginate(15);

        return SubmissionResource::collection($submissions);
    }

    /**
     * POST /api/module-items/{moduleItem}/submissions
     * Submit an assignment or quiz
     */
    public function store(Request $request, ModuleItem $moduleItem)
    {
        $this->authorize('create', [Submission::class, $moduleItem]);

        // Validate submission based on module item type
        $rules = $this->getSubmissionRules($moduleItem);
        $data = $this->validated($request, $rules);

        // Check if student has exceeded max attempts
        if ($moduleItem->settings['max_attempts'] ?? null) {
            $attempts = $moduleItem->submissions()
                ->where('user_id', $request->user()->id)
                ->count();

            if ($attempts >= $moduleItem->settings['max_attempts']) {
                return response()->json([
                    'message' => 'Maximum number of attempts reached'
                ], Response::HTTP_FORBIDDEN);
            }
        }

        // Handle late submission
        if ($moduleItem->due_date && now()->gt($moduleItem->due_date)) {
            if (!($moduleItem->settings['allow_late_submission'] ?? false)) {
                return response()->json([
                    'message' => 'Late submissions are not allowed'
                ], Response::HTTP_FORBIDDEN);
            }

            // Apply late submission penalty if configured
            if (isset($moduleItem->settings['late_submission_penalty'])) {
                $data['late_penalty'] = $moduleItem->settings['late_submission_penalty'];
            }
        }

        // Create submission
        $submission = $moduleItem->submissions()->create([
            'user_id' => $request->user()->id,
            'content' => $data['content'],
            'submission_type' => $moduleItem->submission_type,
            'late_penalty' => $data['late_penalty'] ?? 0,
            'status' => 'submitted',
        ]);

        // Auto-grade quiz submissions
        if ($moduleItem->isQuiz()) {
            $this->autoGradeQuiz($submission, $moduleItem);
        }

        return new SubmissionResource($submission);
    }

    /**
     * Get validation rules based on module item type
     */
    protected function getSubmissionRules(ModuleItem $moduleItem): array
    {
        $rules = [];

        switch ($moduleItem->submission_type) {
            case 'file':
                $rules = [
                    'content.file' => 'required|file|max:10240', // 10MB max
                    'content.file_name' => 'required|string|max:255',
                    'content.file_type' => 'required|string|max:100',
                ];
                break;

            case 'essay':
                $rules = [
                    'content.text' => 'required|string|min:100',
                    'content.word_count' => 'required|integer|min:1',
                ];
                break;

            case 'quiz':
                $rules = [
                    'content.answers' => 'required|array',
                    'content.answers.*.question_id' => 'required|exists:questions,id',
                    'content.answers.*.answer' => 'required',
                    'content.time_taken' => 'required|integer|min:0',
                ];
                break;
        }

        return $rules;
    }

    /**
     * Auto-grade a quiz submission
     */
    protected function autoGradeQuiz(Submission $submission, ModuleItem $moduleItem): void
    {
        $score = 0;
        $totalQuestions = $moduleItem->questions()->count();
        $answers = collect($submission->content['answers']);

        foreach ($moduleItem->questions as $question) {
            $answer = $answers->firstWhere('question_id', $question->id);
            
            if (!$answer) {
                continue;
            }

            if ($question->isCorrect($answer['answer'])) {
                $score++;
            }
        }

        $grade = ($score / $totalQuestions) * $moduleItem->max_score;
        
        // Apply late penalty if any
        if ($submission->late_penalty > 0) {
            $grade = $grade * (1 - ($submission->late_penalty / 100));
        }

        $submission->update([
            'grade' => round($grade, 2),
            'status' => 'graded',
            'graded_at' => now(),
            'graded_by' => 'system',
        ]);
    }

    /**
     * GET /api/courses/{course}/module-items/{item}/submissions/{submission}
     * Show a single submission (owner or instructor).
     */
    public function show(Course $course, ModuleItem $moduleItem, Submission $submission)
    {
        $this->authorize('view', $submission);

        $submission->load(['grade', 'submissionAnswers']);

        return new SubmissionResource($submission);
    }

    /**
     * PUT /api/courses/{course}/module-items/{item}/submissions/{submission}
     * Grade or re-grade a submission (instructor only).
     */
    public function update(Request $request, Course $course, ModuleItem $moduleItem, Submission $submission)
    {
        $this->authorize('update', $submission);

        $data = $this->validated($request, [
            'content' => 'sometimes|required|string',
            'file_path' => 'nullable|string',
            'answers' => 'nullable|array',
        ]);

        $submission->update($data);

        return new SubmissionResource($submission);
    }

    /**
     * GET /api/submissions/my-submissions
     * List current student's own submissions.
     */
    public function mySubmissions(Request $request)
    {
        $user = $request->user();
        
        $submissions = Submission::forUser($user->id)
            ->with([
                'moduleItem' => function ($query) {
                    $query->with(['module.course', 'questions.options']);
                },
                'submissionAnswers.question',
                'submissionAnswers.question.options'
            ])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return SubmissionResource::collection($submissions);
    }

    /**
     * DELETE /api/courses/{course}/module-items/{item}/submissions/{submission}
     * Delete a submission (owner only).
     */
    public function destroy(Course $course, ModuleItem $moduleItem, Submission $submission)
    {
        $this->authorize('delete', $submission);

        $submission->delete();

        return $this->respond(null, 'Submission deleted', Response::HTTP_NO_CONTENT);
    }
}
