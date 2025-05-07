<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assignment extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'course_id',
        'title',
        'description',
        'due_date',
        'max_score',
        'submission_type',   // 'file', 'essay', or 'quiz'
        'settings',          // JSON for quiz parameters, time limits, etc.
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'due_date'        => 'datetime',
        'max_score'       => 'decimal:2',
        'settings'        => 'array',
    ];

    /**
     * The course this assignment belongs to.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * All submissions made for this assignment.
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    /**
     * If this is a quiz assignment, its questions.
     */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    /**
     * Checkers for submission type.
     */
    public function isQuiz(): bool
    {
        return $this->submission_type === 'quiz';
    }

    public function isEssay(): bool
    {
        return $this->submission_type === 'essay';
    }

    public function isFileUpload(): bool
    {
        return $this->submission_type === 'file';
    }

    /**
     * Scope: assignments due on or after now.
     */
    public function scopeDueSoon($query)
    {
        return $query->where('due_date', '>=', now());
    }

    /**
     * Scope: assignments past their due date.
     */
    public function scopePastDue($query)
    {
        return $query->where('due_date', '<', now());
    }
}
