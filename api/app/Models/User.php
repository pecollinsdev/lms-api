<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Notifications\ModuleItemDueSoon;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'profile_picture', 'bio',
        'phone_number', 'instructor_code', 'academic_specialty', 'qualifications'
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class, 'instructor_id');
    }

    public function enrolledCourses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'enrollments', 'user_id', 'course_id')->withTimestamps();
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    public function progress(): HasMany
    {
        return $this->hasMany(Progress::class);
    }

    public function isInstructor(): bool
    {
        return $this->role === 'instructor';
    }

    public function isStudent(): bool
    {
        return $this->role === 'student';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [];
    }

    public function getEnrolledCourses(bool $withStats = false, bool $withInstructor = false): Collection
    {
        return $this->enrolledCourses()
            ->when($withStats, fn($q) => $q->withCount(['students', 'modules', 'announcements', 'assignments', 'quizzes']))
            ->when($withInstructor, fn($q) => $q->with(['instructor:id,name,email,profile_picture']))
            ->get();
    }

    protected function getUpcomingAssignmentsQuery(int $daysAhead)
    {
        return ModuleItem::whereHas('module.course.enrollments', fn($q) => $q->where('user_id', $this->id))
            ->where('type', 'assignment')
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [now(), now()->addDays($daysAhead)])
            ->with('module.course')
            ->orderBy('due_date');
    }

    public function getCalendarData(bool $includeDeadlines = true, bool $includeCourses = true, int $daysAhead = 14): array
    {
        return [
            'deadlines' => $includeDeadlines ? $this->getUpcomingAssignmentsQuery($daysAhead)->get()->map(fn($item) => [
                'id' => $item->id,
                'title' => $item->title,
                'due_date' => $item->due_date,
                'course_id' => $item->module->course_id,
                'course_title' => $item->module->course->title,
            ]) : [],

            'courses' => $includeCourses ? $this->enrolledCourses->map(fn($course) => [
                'id' => $course->id,
                'title' => $course->title,
                'start_date' => $course->start_date,
                'end_date' => $course->end_date,
            ]) : []
        ];
    }

    public function getProgressAndSubmissions(bool $includeSubmissions = true, bool $includeProgress = true, int $limit = 5): array
    {
        return [
            'submissions' => $includeSubmissions ? $this->submissions()->with('moduleItem.module.course')->latest()->take($limit)->get()->map(fn($s) => [
                'id' => $s->id,
                'module_item_title' => $s->moduleItem->title,
                'course_title' => $s->moduleItem->module->course->title,
                'status' => $s->status,
                'score' => $s->score,
                'letter_grade' => $s->score ? (new Grade())->calculateLetterGrade($s->score) : null,
                'submitted_at' => $s->submitted_at,
            ]) : [],

            'progress' => $includeProgress ? $this->progress()->with('moduleItem.module.course')->get()->map(fn($p) => [
                'module_item_title' => $p->moduleItem->title,
                'course_title' => $p->moduleItem->module->course->title,
                'status' => $p->status,
                'score' => $p->score,
                'completed_at' => $p->completed_at,
            ]) : []
        ];
    }

    public function checkUpcomingDeadlines(bool $send = true, int $daysAhead = 1): array
    {
        $dueItems = $this->getUpcomingAssignmentsQuery($daysAhead)->get();
        $notified = [];

        if ($send) {
            foreach ($dueItems as $item) {
                if (!$this->notifications()->where('type', ModuleItemDueSoon::class)->where('data->module_item_id', $item->id)->exists()) {
                    $this->notify(new ModuleItemDueSoon($item));
                    $notified[] = $item->id;
                }
            }
        }

        return [
            'items' => $dueItems->map(fn($i) => [
                'id' => $i->id,
                'title' => $i->title,
                'due_date' => $i->due_date,
                'course_title' => $i->module->course->title
            ]),
            'notified_items' => $notified
        ];
    }

    public function handleNotifications(?string $action = null, ?string $id = null, int $perPage = 10)
    {
        return match ($action) {
            'read' => $this->markNotificationAsRead($id),
            'unread' => $this->getUnreadNotifications(),
            'mark-all-read' => $this->markAllNotificationsAsRead(),
            default => $this->notifications()->latest()->paginate($perPage),
        };
    }

    public static function createOrUpdateUser(array $data, ?\Illuminate\Http\UploadedFile $profilePicture = null, ?self $user = null): self
    {
        if ($profilePicture) {
            $data['profile_picture'] = $profilePicture->store('profiles', 'public');
        }

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        return $user ? tap($user)->update($data) : static::create($data);
    }

    public static function findByEmail(string $email): ?self
    {
        return static::where('email', $email)->first();
    }

    public static function validateAndUseInstructorCode(string $code): bool
    {
        $valid = DB::table('instructor_codes')->where('code', $code)->where('used', false)->first();

        if (!$valid) return false;

        DB::table('instructor_codes')->where('code', $code)->update(['used' => true]);
        return true;
    }

    public function validateCredentials(string $password): bool
    {
        return Hash::check($password, $this->password);
    }

    public function handleNotificationsAndDeadlines(array $options = []): array
    {
        $opts = array_merge([
            'check_deadlines' => true,
            'send_notifications' => true,
            'days_ahead' => 1,
            'notification_types' => ['due_soon'],
            'per_page' => 10,
            'mark_read' => false,
            'notification_id' => null,
        ], $options);

        return [
            'notification_action' => $opts['mark_read']
                ? $this->handleNotifications('read', $opts['notification_id'])
                : ($opts['notification_types'] ? $this->handleNotifications(null, null, $opts['per_page']) : null),

            'deadlines' => $opts['check_deadlines']
                ? $this->checkUpcomingDeadlines($opts['send_notifications'], $opts['days_ahead'])
                : []
        ];
    }

    public function recentActivities()
    {
        return $this->hasMany(Activity::class)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();
    }

    public function upcomingDeadlines()
    {
        return ModuleItem::whereHas('module.course.enrollments', function ($query) {
            $query->where('user_id', $this->id);
        })
        ->where('due_date', '>', now())
        ->orderBy('due_date', 'asc')
        ->take(5)
        ->get();
    }
}
