<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Progress extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'module_item_id',
        'status',
        'completed_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The module item this progress belongs to.
     */
    public function moduleItem()
    {
        return $this->belongsTo(ModuleItem::class);
    }

    /**
     * Mark progress as in progress.
     */
    public function markInProgress() {
        $this->update(['status' => 'in_progress']);
    }

    /**
     * Mark progress as submitted.
     */
    public function markSubmitted() {
        $this->update(['status' => 'submitted', 'completed_at' => now()]);
    }

    /**
     * Mark progress as graded.
     */
    public function markGraded($score, $letter) {
        $this->update([
            'status' => 'graded',
            'score' => $score,
            'letter_grade' => $letter,
            'completed_at' => now(),
        ]);
    }

    /**
     * Get progress records for a module item
     */
    public static function getForModuleItem(int $moduleItemId)
    {
        return self::where('module_item_id', $moduleItemId)
            ->with('user')
            ->paginate(15);
    }

    /**
     * Get progress records for a user
     */
    public static function getForUser(int $userId)
    {
        return self::where('user_id', $userId)
            ->with('moduleItem.module.course')
            ->paginate(15);
    }

    /**
     * Create progress for a module item
     */
    public static function createForModuleItem(int $userId, int $moduleItemId, string $status)
    {
        return self::create([
            'user_id' => $userId,
            'module_item_id' => $moduleItemId,
            'status' => $status,
            'completed_at' => $status === 'graded' ? now() : null,
        ]);
    }
}
