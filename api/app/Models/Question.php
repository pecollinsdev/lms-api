<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'assignment_id',
        'type',        // 'multiple_choice' or 'text'
        'prompt',      // the question text
        'order',       // display order within the assignment
        'points',      // maximum score for this question
        'settings',    // JSON for MC settings (e.g. allow_multiple)
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'order'    => 'integer',
        'points'   => 'decimal:2',
        'settings' => 'array',
    ];

    /**
     * The assignment this question belongs to.
     */
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    /**
     * The possible options (for multiple‐choice).
     */
    public function options(): HasMany
    {
        return $this->hasMany(Option::class);
    }

    /**
     * The student answers submitted for this question.
     */
    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }

    /**
     * Determine if this is a multiple‐choice question.
     */
    public function isMultipleChoice(): bool
    {
        return $this->type === 'multiple_choice';
    }

    /**
     * Determine if this is a free‐text question.
     */
    public function isText(): bool
    {
        return $this->type === 'text';
    }
}
