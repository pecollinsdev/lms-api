<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Collection;

class Announcement extends Model
{
    use HasFactory;

    protected $table = 'announcements';

    protected $fillable = [
        'course_id',
        'created_by',
        'title',
        'content',
        'is_pinned'
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
    ];

    /**
     * Get the course that owns the announcement.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id', 'id');
    }

    /**
     * Get the user who created the announcement.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope a query to only include announcements for specific courses.
     */
    public function scopeForCourses($query, $courseIds)
    {
        return $query->whereIn('course_id', $courseIds);
    }

    /**
     * Scope a query to order by pinned status and creation date.
     */
    public function scopeOrderByPinnedAndDate($query)
    {
        return $query->orderBy('is_pinned', 'desc')
                    ->orderBy('created_at', 'desc');
    }

    /**
     * Get announcements for a student's enrolled courses
     * 
     * @param User $student The student to get announcements for
     * @param int $limit Maximum number of announcements to return
     * @param bool $detailed Whether to return detailed information (includes course and creator details)
     * @return Collection|array
     */
    public static function getStudentAnnouncements(User $student, int $limit = 10, bool $detailed = true): Collection|array
    {
        $query = self::select([
                'announcements.id',
                'announcements.title',
                'announcements.content',
                'announcements.is_pinned',
                'announcements.created_at',
                'announcements.course_id',
                'announcements.created_by'
            ])
            ->forCourses($student->enrolledCourses()->pluck('courses.id'))
            ->with([
                'course:id,title',
                'creator:id,name,role'
            ])
            ->orderByPinnedAndDate()
            ->take($limit);

        $announcements = $query->get();

        if (!$detailed) {
            return $announcements;
        }

        return $announcements->map(function ($announcement) {
            return [
                'id' => $announcement->id,
                'title' => $announcement->title,
                'content' => $announcement->content,
                'course_title' => $announcement->course->title,
                'created_at' => $announcement->created_at,
                'is_pinned' => $announcement->is_pinned,
                'creator' => [
                    'name' => $announcement->creator->name,
                    'role' => $announcement->creator->role,
                ],
            ];
        });
    }

    /**
     * Create a new announcement for a course
     */
    public static function createAnnouncement(array $data, int $courseId, int $createdById): self
    {
        return static::create([
            'course_id' => $courseId,
            'created_by' => $createdById,
            'title' => $data['title'],
            'content' => $data['content'],
            'is_pinned' => $data['is_pinned'] ?? false,
        ]);
    }
} 