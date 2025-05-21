<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;

class Course extends Model
{
    use HasFactory, SoftDeletes;

    // Enrollment statuses
    public const STATUS_ACTIVE   = 'active';

    protected $fillable = [
        'title',
        'slug',
        'description',
        'instructor_id',
        'start_date',
        'end_date',
        'is_published',
        'cover_image',
    ];

    protected $casts = [
        'start_date'   => 'datetime',
        'end_date'     => 'datetime',
        'is_published' => 'boolean',
    ];

    // -----------------------------------------------------------------------------
    // Relations
    // -----------------------------------------------------------------------------

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'enrollments')
                    ->withPivot(['enrolled_at', 'status', 'enrolled_by'])
                    ->withTimestamps();
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function modules(): HasMany
    {
        return $this->hasMany(Module::class);
    }

    public function moduleItems(): HasManyThrough
    {
        return $this->hasManyThrough(ModuleItem::class, Module::class);
    }

    public function assignments(): HasManyThrough
    {
        return $this->hasManyThrough(ModuleItem::class, Module::class)
                    ->where('type', 'assignment');
    }

    public function quizzes(): HasManyThrough
    {
        return $this->hasManyThrough(ModuleItem::class, Module::class)
                    ->where('type', 'quiz');
    }

    public function submissions(): HasManyThrough
    {
        return $this->hasManyThrough(Submission::class, ModuleItem::class, 'module_id', 'module_item_id');
    }

    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class);
    }

    // -----------------------------------------------------------------------------
    // Scopes
    // -----------------------------------------------------------------------------

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeForInstructor(Builder $query, int $instructorId): Builder
    {
        return $query->where('instructor_id', $instructorId);
    }

    public function scopeWithStats(Builder $query): Builder
    {
        return $query->withCount([
            'students',
            'modules',
            'moduleItems as total_items',
            'submissions'
        ]);
    }

    // -----------------------------------------------------------------------------
    // Checks
    // -----------------------------------------------------------------------------

    public function isEnrollmentOpen(): bool
    {
        return now()->lt($this->end_date);
    }

    public function isEnrolled(User $student): bool
    {
        return $this->students()->where('user_id', $student->id)->exists();
    }

    // -----------------------------------------------------------------------------
    // Data Retrieval
    // -----------------------------------------------------------------------------

    /**
     * Retrieve course statistics.
     *
     * @param  array<string,bool>  $options
     * @return array<string,mixed>
     */
    public function getStatistics(array $options = []): array
    {
        $opts = array_merge([
            'student_count' => true,
            'module_count'  => true,
            'item_count'    => true,
            'completion'    => false,
            'grade'         => false,
        ], $options);

        $stats = [];

        if ($opts['student_count']) {
            $stats['student_count'] = $this->students()->count();
        }

        if ($opts['module_count']) {
            $stats['module_count'] = $this->modules()->count();
        }

        if ($opts['item_count']) {
            $stats['total_items'] = $this->moduleItems()->count();
        }

        if ($opts['completion']) {
            $stats['completion'] = $this->getCompletionStats();
        }

        if ($opts['grade']) {
            $stats['grade'] = $this->getGradeStats();
        }

        return $stats;
    }

    /**
     * Compute completion percentages for all enrolled students.
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getCompletionStats(): Collection
    {
        $totalItems = $this->moduleItems()->count();

        $students = $this->students()->get();

        foreach ($students as $student) {
            $completedCount = 0;
            foreach ($this->moduleItems as $item) {
                if (in_array($item->type, ['assignment', 'quiz'])) {
                    if ($item->submissions()->where('user_id', $student->id)->where('status', 'graded')->exists()) {
                        $completedCount++;
                    }
                } else {
                    if ($item->progress()->where('user_id', $student->id)->where('status', 'completed')->exists()) {
                        $completedCount++;
                    }
                }
            }
            $student->completed_count = $completedCount;
            $student->completion_percentage = $totalItems > 0
                ? round($completedCount / $totalItems * 100)
                : 0;
        }

        return $students;
    }

    /**
     * Compute average grades for all enrolled students.
     */
    protected function getGradeStats(): Collection
    {
        $grades = Grade::whereIn('module_item_id', $this->moduleItems()->pluck('module_items.id'))
            ->where('is_final', true)
            ->groupBy('user_id')
            ->select('user_id', DB::raw('AVG(score) as average_grade'))
            ->get();

        $gradeModel = new Grade();
        foreach ($grades as $grade) {
            $grade->letter_grade = $gradeModel->calculateLetterGrade($grade->average_grade);
        }

        return $grades;
    }

    /**
     * Paginate student data, optionally with completed counts.
     */
    public function getStudentData(bool $withCompleted = false, int $perPage = 15)
    {
        $query = $this->students()
            ->select(['users.id', 'users.name', 'users.email'])
            ->withPivot(['enrolled_at', 'status']);

        if ($withCompleted) {
            $query->withCount(['progress as completed_count' => fn(Builder $q) =>
                $q->where('status', 'completed')
                  ->whereHas('moduleItem.module', fn($m) => $m->where('course_id', $this->id))
            ]);
        }

        return $query->paginate($perPage);
    }

    /**
     * Query courses with optional filters, stats, and instructor details.
     */
    public static function getCourses(array $filters = [], bool $withStats = false, bool $withInstructor = false): Collection
    {
        $query = static::query();

        if (!empty($filters['is_published'])) {
            $query->published();
        }

        if (!empty($filters['instructor_id'])) {
            $query->forInstructor((int)$filters['instructor_id']);
        }

        if (!empty($filters['search'])) {
            $term = "%{$filters['search']}%";
            $query->where(fn($q) =>
                $q->where('title', 'like', $term)
                  ->orWhere('description', 'like', $term)
            );
        }

        if ($withStats) {
            $query->withStats();
        }

        if ($withInstructor) {
            $query->with(['instructor:id,name,email,profile_picture']);
        }

        return $query->get();
    }

    /**
     * Handle enrollment, preventing duplicates and closed courses.
     */
    public function handleEnrollment(User|string $student, array $options = []): ?array
    {
        if (is_string($student)) {
            $student = User::where('email', $student)
                           ->where('role', 'student')
                           ->firstOrFail();
        }

        if (!$this->isEnrollmentOpen() || $this->isEnrolled($student)) {
            return null;
        }

        $attrs = array_merge([
            'enrolled_at' => now(),
            'status'      => self::STATUS_ACTIVE,
            'enrolled_by' => null,
        ], $options);

        $this->students()->attach($student->id, $attrs);

        return $attrs;
    }
}