<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use App\Notifications\ModuleItemSubmitted;

class Submission extends Model
{
    use HasFactory, SoftDeletes;

    // Status constants
    public const STATUS_PENDING = 'pending';
    public const STATUS_GRADED  = 'graded';

    /** @var array<int,string> */
    protected $fillable = [
        'user_id',
        'module_item_id',
        'content',
        'file_path',
        'feedback',
        'status',
        'submission_type', // file, essay, quiz
        'answers',
        'submitted_at',
    ];

    /** @var array<string,string> */
    protected $casts = [
        'submitted_at' => 'datetime',
        'answers'      => 'array',
        'score'        => 'decimal:2',
    ];

    /** Belongs to the submitting user */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Belongs to the module item */
    public function moduleItem(): BelongsTo
    {
        return $this->belongsTo(ModuleItem::class);
    }

    /** Has many quiz answers */
    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }

    /** Scope by status */
    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /** Scope pending submissions */
    public function scopePending(Builder $query): Builder
    {
        return $query->status(self::STATUS_PENDING);
    }

    /** Scope graded submissions */
    public function scopeGraded(Builder $query): Builder
    {
        return $query->status(self::STATUS_GRADED);
    }

    /** Scope by module item */
    public function scopeForModuleItem(Builder $query, int $moduleItemId): Builder
    {
        return $query->where('module_item_id', $moduleItemId);
    }

    /** Scope by user */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /** Check if pending */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /** Check if graded */
    public function isGraded(): bool
    {
        return $this->status === self::STATUS_GRADED;
    }

    /** Mark submission as graded */
    public function markGraded(float $grade, string $feedback): self
    {
        $this->update([
            'status'    => self::STATUS_GRADED,
            'feedback'  => $feedback,
            'grade'     => $grade,
            'graded_at' => now(),
        ]);

        return $this;
    }

    /**
     * Factory method: create submission, track progress, and notify instructor
     *
     * @param  array<string,mixed>  \$data
     * @param  int                  \$userId
     * @param  int                  \$moduleItemId
     * @return self
     */
    public static function createSubmission(array $data, int $userId, int $moduleItemId): self
    {
        return DB::transaction(function () use ($data, $userId, $moduleItemId) {
            $submission = static::create([
                'user_id'         => $userId,
                'module_item_id'  => $moduleItemId,
                'content'         => $data['content'] ?? null,
                'file_path'       => $data['file_path'] ?? null,
                'status'          => self::STATUS_PENDING,
                'submission_type' => $data['submission_type'] ?? null,
                'answers'         => $data['answers'] ?? [],
                'submitted_at'    => now(),
            ]);

            // Track progress
            Progress::create([
                'user_id'        => $userId,
                'module_item_id' => $moduleItemId,
                'status'         => 'submitted',
                'completed_at'   => now(),
            ]);

            // Notify instructor
            $instructor = $submission->moduleItem->module->course->instructor;
            if ($instructor) {
                $instructor->notify(new ModuleItemSubmitted($submission));
            }

            return $submission;
        });
    }
}
