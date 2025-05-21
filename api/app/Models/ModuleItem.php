<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ModuleItem extends Model
{
    use HasFactory;

    // Item types
    public const TYPE_ASSIGNMENT = 'assignment';
    public const TYPE_QUIZ       = 'quiz';
    public const TYPE_VIDEO      = 'video';
    public const TYPE_DOCUMENT   = 'document';

    protected $fillable = [
        'module_id', 'type', 'title', 'description', 'due_date',
        'order', 'max_score', 'submission_type', 'content_data', 'settings'
    ];

    protected $casts = [
        'due_date'     => 'datetime',
        'max_score'    => 'decimal:2',
        'content_data' => 'json',
        'settings'     => 'array',
        'order'        => 'integer',
    ];

    protected $appends = ['content'];

    // Relations
    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function progress(): HasMany
    {
        return $this->hasMany(\App\Models\Progress::class);
    }

    // Accessors
    public function getContentAttribute(): array
    {
        $data = $this->content_data;
        if (is_string($data)) {
            $data = json_decode($data, true);
        }
        if (empty($data)) {
            return [];
        }
        switch ($this->type) {
            case 'video':
                return [
                    'url' => $data['video_url'] ?? null,
                    'provider' => $data['video_provider'] ?? null,
                    'duration' => $data['video_duration'] ?? null,
                    'allow_download' => $data['video_allow_download'] ?? false,
                ];
            case 'document':
                return [
                    'url' => $data['document_url'] ?? null,
                    'type' => $data['document_type'] ?? null,
                    'size' => $data['document_size'] ?? null,
                    'allow_download' => $data['allow_download'] ?? false,
                ];
            case 'assignment':
                return [
                    'instructions' => $data['instructions'] ?? null,
                    'due_date' => $data['due_date'] ?? null,
                    'max_score' => $data['max_score'] ?? null,
                    // Optionally add settings if present
                    'max_attempts' => $data['max_attempts'] ?? ($this->settings['max_attempts'] ?? null),
                    'allow_late_submission' => $data['allow_late_submission'] ?? ($this->settings['allow_late_submission'] ?? false),
                    'late_submission_penalty' => $data['late_submission_penalty'] ?? ($this->settings['late_submission_penalty'] ?? null),
                ];
            case 'quiz':
                return [
                    'instructions' => $data['instructions'] ?? null,
                    'time_limit' => $data['time_limit'] ?? null,
                    'max_attempts' => $data['max_attempts'] ?? null,
                    'allow_retake' => $data['allow_retake'] ?? false,
                    'show_correct_answers' => $data['show_correct_answers'] ?? false,
                    'passing_score' => $data['passing_score'] ?? null,
                ];
            default:
                return [];
        }
    }

    // Type checks
    public function isType(string $type): bool
    {
        return $this->type === $type;
    }

    public function isQuiz(): bool
    {
        return $this->isType(self::TYPE_QUIZ);
    }

    public function isAssignment(): bool
    {
        return $this->isType(self::TYPE_ASSIGNMENT);
    }

    public function isVideo(): bool
    {
        return $this->isType(self::TYPE_VIDEO);
    }

    public function isDocument(): bool
    {
        return $this->isType(self::TYPE_DOCUMENT);
    }

    public function isSubmittable(): bool
    {
        return $this->isAssignment() || $this->isQuiz();
    }

    // Scopes
    public function scopeForStudent(Builder $query, User $student): Builder
    {
        return $query->whereHas('module.course.enrollments', fn($q) => $q->where('user_id', $student->id));
    }

    public function scopeSubmittable(Builder $query): Builder
    {
        return $query->whereIn('type', [self::TYPE_ASSIGNMENT, self::TYPE_QUIZ]);
    }

    public function scopeDueBetween(Builder $query, $from, $to): Builder
    {
        return $query->whereBetween('due_date', [$from, $to]);
    }

    public function loadSubmissionsForUser(User $user): self
    {
        $this->load(['submissions' => function ($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->latest()
                  ->limit(1);
        }]);

        return $this;
    }

    // Retrieve upcoming deadlines for a student
    public static function getUpcomingDeadlinesForStudent(User $student, int $daysAhead = 14): array
    {
        return static::query()
            ->forStudent($student)
            ->submittable()
            ->dueBetween(now(), now()->addDays($daysAhead))
            ->with('module.course')
            ->orderBy('due_date')
            ->get()
            ->map(fn($item) => [
                'id'           => $item->id,
                'title'        => $item->title,
                'due_date'     => $item->due_date,
                'course_id'    => $item->module->course_id,
                'course_title' => $item->module->course->title,
            ])
            ->toArray();
    }

    // Get calendar data for a student
    public static function getCalendarDataForStudent(User $student): array
    {
        return static::query()
            ->forStudent($student)
            ->submittable()
            ->with('module.course')
            ->get(['id', 'title', 'module_id', 'due_date'])
            ->map(fn($item) => [
                'id'        => $item->id,
                'title'     => $item->title,
                'module_id' => $item->module_id,
                'due_date'  => $item->due_date,
            ])
            ->toArray();
    }
}