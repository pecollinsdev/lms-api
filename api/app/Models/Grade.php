<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Grade extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'module_item_id',
        'submission_id',
        'graded_by',
        'score',
        'letter_grade',
        'feedback',
        'rubric_scores',
        'graded_at',
        'is_final',
    ];

    protected $casts = [
        'score' => 'float',
        'rubric_scores' => 'array',
        'graded_at' => 'datetime',
        'is_final' => 'boolean',
    ];

    /**
     * Get the student who received this grade.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the module item this grade is for.
     */
    public function moduleItem(): BelongsTo
    {
        return $this->belongsTo(ModuleItem::class);
    }

    /**
     * Get the submission this grade is for.
     */
    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    /**
     * Get the instructor who gave this grade.
     */
    public function grader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    /**
     * Scope a query to only include final grades.
     */
    public function scopeFinal(Builder $query): Builder
    {
        return $query->where('is_final', true);
    }

    /**
     * Scope a query to only include grades for a specific user.
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to only include grades for a specific module item.
     */
    public function scopeForModuleItem(Builder $query, int $moduleItemId): Builder
    {
        return $query->where('module_item_id', $moduleItemId);
    }

    /**
     * Calculate letter grade based on score.
     */
    public function calculateLetterGrade(float $score): string
    {
        if ($score >= 93) return 'A';
        if ($score >= 90) return 'A-';
        if ($score >= 87) return 'B+';
        if ($score >= 83) return 'B';
        if ($score >= 80) return 'B-';
        if ($score >= 77) return 'C+';
        if ($score >= 73) return 'C';
        if ($score >= 70) return 'C-';
        if ($score >= 67) return 'D+';
        if ($score >= 65) return 'D';
        return 'F';
    }

    /**
     * Calculate GPA based on letter grade.
     */
    public static function calculateGPAFromLetterGrade(string $letterGrade): float
    {
        return match($letterGrade) {
            'A' => 4.0,
            'A-' => 3.7,
            'B+' => 3.3,
            'B' => 3.0,
            'B-' => 2.7,
            'C+' => 2.3,
            'C' => 2.0,
            'C-' => 1.7,
            'D+' => 1.3,
            'D' => 1.0,
            default => 0.0
        };
    }

    /**
     * Calculate GPA for a user based on their grades.
     */
    public static function calculateGPAForUser(int $userId): ?float
    {
        $grades = static::where('user_id', $userId)
            ->where('is_final', true)
            ->get();
        
        if ($grades->isEmpty()) {
            return null;
        }

        $totalGradePoints = 0;
        $totalCourses = 0;

        foreach ($grades as $grade) {
            $totalGradePoints += static::calculateGPAFromLetterGrade($grade->letter_grade);
            $totalCourses++;
        }

        return $totalCourses > 0 ? round($totalGradePoints / $totalCourses, 2) : null;
    }

    /**
     * Set the letter grade based on the score.
     */
    public function setLetterGradeFromScore(): void
    {
        $this->letter_grade = $this->calculateLetterGrade($this->score);
    }

    /**
     * Unmark all final grades for a user and module item.
     */
    public static function unmarkFinalGrades(int $userId, int $moduleItemId): void
    {
        static::where('user_id', $userId)
            ->where('module_item_id', $moduleItemId)
            ->where('is_final', true)
            ->update(['is_final' => false]);
    }

    /**
     * Calculate statistics for grades of a module item
     */
    public static function calculateStatistics(int $moduleItemId): array
    {
        $grades = static::forModuleItem($moduleItemId)
            ->final()
            ->get();

        return [
            'total_grades' => $grades->count(),
            'average_score' => $grades->avg('score'),
            'highest_score' => $grades->max('score'),
            'lowest_score' => $grades->min('score'),
            'grade_distribution' => [
                'A' => $grades->where('letter_grade', 'A')->count(),
                'B' => $grades->where('letter_grade', 'B')->count(),
                'C' => $grades->where('letter_grade', 'C')->count(),
                'D' => $grades->where('letter_grade', 'D')->count(),
                'F' => $grades->where('letter_grade', 'F')->count(),
            ],
        ];
    }

    /**
     * Create a new grade with the given data
     */
    public static function createGrade(array $data, int $graderId): self
    {
        // If this is a final grade, unmark any existing final grades
        if ($data['is_final'] ?? true) {
            static::unmarkFinalGrades($data['user_id'], $data['module_item_id']);
        }

        $grade = static::create([
            'user_id' => $data['user_id'],
            'module_item_id' => $data['module_item_id'],
            'submission_id' => $data['submission_id'] ?? null,
            'graded_by' => $graderId,
            'score' => $data['score'],
            'feedback' => $data['feedback'] ?? null,
            'rubric_scores' => $data['rubric_scores'] ?? null,
            'graded_at' => now(),
            'is_final' => $data['is_final'] ?? true,
        ]);

        $grade->setLetterGradeFromScore();
        $grade->save();

        // Update the submission status if a submission was provided
        if ($grade->submission_id) {
            $grade->submission->update(['status' => 'graded']);
        }

        return $grade;
    }

    /**
     * Update the grade with new data
     */
    public function updateGrade(array $data): self
    {
        if (isset($data['score'])) {
            $this->score = $data['score'];
            $this->setLetterGradeFromScore();
        }

        if (isset($data['feedback'])) {
            $this->feedback = $data['feedback'];
        }

        if (isset($data['rubric_scores'])) {
            $this->rubric_scores = $data['rubric_scores'];
        }

        if (isset($data['is_final'])) {
            // If making this the final grade, unmark any existing final grades
            if ($data['is_final']) {
                static::unmarkFinalGrades($this->user_id, $this->module_item_id);
            }
            $this->is_final = $data['is_final'];
        }

        $this->save();

        return $this;
    }
} 