<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Enrollment extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'enrollments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'user_id',
        'course_id',
        'enrolled_at',
        'status',       // e.g. 'pending', 'active', 'completed'
    ];

    /**
     * Attribute casting.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'enrolled_at' => 'datetime',
    ];

    /**
     * The student (user) who is enrolled.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * The course this enrollment belongs to.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    /**
     * Find an enrollment by user and course IDs
     */
    public static function findByUserAndCourse(int $userId, int $courseId): ?self
    {
        return static::where([
            'user_id' => $userId,
            'course_id' => $courseId,
        ])->first();
    }

    /**
     * Check if the enrollment is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if the enrollment is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the enrollment is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
}
