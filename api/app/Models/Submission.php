<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Submission extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'assignment_id',
        'user_id',
        'submission_type',   // 'file', 'essay', 'quiz'
        'file_path',         // for file uploads
        'content',           // for text/essay submissions
        'answers',           // JSON column for quiz responses
        'submitted_at',
        'grade',
        'feedback',
        'status',            // 'pending', 'graded', 'late', etc.
        'graded_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'submitted_at' => 'datetime',
        'graded_at'    => 'datetime',
        'grade'        => 'decimal:2',
        'answers'      => 'array',
        'content'      => 'string',
    ];

    /**
     * The assignment this submission belongs to.
     */
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    /**
     * The student (user) who made this submission.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scope a query to only pending submissions.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only graded submissions.
     */
    public function scopeGraded($query)
    {
        return $query->where('status', 'graded');
    }

    /**
     * Mark this submission as graded.
     *
     * @param  float   $grade
     * @param  string  $feedback
     * @return void
     */
    public function markGraded(float $grade, string $feedback): void
    {
        $this->update([
            'grade'      => $grade,
            'feedback'   => $feedback,
            'status'     => 'graded',
            'graded_at'  => now(),
        ]);
    }
}
