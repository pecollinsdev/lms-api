<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Course extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'title',
        'slug',            // URL‐friendly identifier
        'description',
        'instructor_id',   // foreign key to users table
        'start_date',
        'end_date',
        'is_published',    // boolean flag
        'cover_image',     // optional course image path
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'start_date'   => 'date',
        'end_date'     => 'date',
        'is_published' => 'boolean',
    ];

    /**
     * The instructor who owns this course.
     */
    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    /**
     * Assignments belonging to this course.
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    /**
     * Students enrolled in this course.
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'enrollments',
            'course_id',
            'user_id'
        )
        ->withTimestamps();
    }

    /**
     * All submissions across all assignments in this course.
     */
    public function submissions(): HasManyThrough
    {
        return $this->hasManyThrough(
            Submission::class,
            Assignment::class,
            'course_id',    // Foreign key on assignments table...
            'assignment_id' // Foreign key on submissions table...
        );
    }

    /**
     * Scope a query to only published courses.
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Determine if enrollment is open (before end_date).
     */
    public function isEnrollmentOpen(): bool
    {
        return now()->lt($this->end_date);
    }
}
