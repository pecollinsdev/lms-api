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
        'module_item_id',
        'type',        // 'multiple_choice' or 'text'
        'prompt',      // the question text
        'order',       // display order within the module item
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
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // When a question is deleted, also delete its options
        static::deleting(function ($question) {
            if ($question->isForceDeleting()) {
                // If force deleting, delete options permanently
                $question->options()->forceDelete();
            } else {
                // If soft deleting, soft delete options
                $question->options()->delete();
            }
        });
    }

    /**
     * The module item this question belongs to.
     */
    public function moduleItem(): BelongsTo
    {
        return $this->belongsTo(ModuleItem::class);
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
