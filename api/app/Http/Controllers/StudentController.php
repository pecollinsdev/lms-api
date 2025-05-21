<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\ModuleItem;
use App\Models\Submission;
use App\Models\Progress;
use App\Models\User;
use Carbon\Carbon;
use App\Notifications\ModuleItemDueSoon;
use App\Models\Announcement;
use App\Models\Grade;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Module;
use App\Services\ActivityService;

class StudentController extends Controller
{
    protected $activityService;

    public function __construct(ActivityService $activityService)
    {
        $this->activityService = $activityService;
    }

    /**
     * Get the student's dashboard data.
     */
    public function dashboard()
    {
        $user = Auth::user();
        
        // Get enrolled courses with their basic info
        $enrollments = $user->enrolledCourses()
            ->with(['instructor:id,name,email,profile_picture'])
            ->get();
        
        // Get recent activities
        $recentActivities = $user->recentActivities();
        
        // Get upcoming deadlines
        $upcomingDeadlines = $user->upcomingDeadlines();
        
        return response()->json([
            'data' => [
                'enrollments' => $enrollments,
                'recent_activities' => $recentActivities,
                'upcoming_deadlines' => $upcomingDeadlines
            ]
        ]);
    }

    public function courses()
    {
        $user = Auth::user();
        $courses = $user->enrolledCourses()
            ->with(['modules', 'instructor:id,name,email,profile_picture'])
            ->get();

        return response()->json(['data' => $courses]);
    }

    public function courseDetails($courseId)
    {
        $user = Auth::user();
        $course = Course::with(['modules', 'instructor:id,name,email,profile_picture'])
            ->whereHas('enrollments', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->findOrFail($courseId);

        return response()->json(['data' => $course]);
    }

    public function courseModules($courseId)
    {
        $user = Auth::user();
        $modules = Module::with(['moduleItems' => function ($query) {
                $query->orderBy('order', 'asc');
            }])
            ->whereHas('course.enrollments', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->where('course_id', $courseId)
            ->orderBy('order', 'asc')
            ->get();

        return response()->json(['data' => $modules]);
    }

    public function courseProgress($courseId)
    {
        $user = Auth::user();
        $progress = Progress::where('user_id', $user->id)
            ->whereHas('moduleItem.module', function ($query) use ($courseId) {
                $query->where('course_id', $courseId);
            })
            ->with(['moduleItem'])
            ->get();

        $totalItems = ModuleItem::whereHas('module', function ($query) use ($courseId) {
            $query->where('course_id', $courseId);
        })->count();

        $completedItems = $progress->where('status', 'completed')->count();
        $progressPercentage = $totalItems > 0 ? round(($completedItems / $totalItems) * 100) : 0;

        return response()->json([
            'data' => [
                'items' => $progress,
                'progress_percentage' => $progressPercentage,
                'completed_items' => $completedItems,
                'total_items' => $totalItems
            ]
        ]);
    }

    public function courseStatistics($courseId)
    {
        $user = Auth::user();
        $submissions = Submission::where('user_id', $user->id)
            ->whereHas('moduleItem.module', function ($query) use ($courseId) {
                $query->where('course_id', $courseId);
            })
            ->with(['moduleItem'])
            ->get();

        $totalScore = 0;
        $maxPossibleScore = 0;

        foreach ($submissions as $submission) {
            if ($submission->status === 'graded') {
                $maxScore = $submission->moduleItem->max_score ?? 0;
                $maxPossibleScore += $maxScore;
                $totalScore += $submission->score ?? 0;
            }
        }

        $gradePercentage = $maxPossibleScore > 0 ? round(($totalScore / $maxPossibleScore) * 100) : 0;
        $letterGrade = $this->calculateLetterGrade($gradePercentage);

        return response()->json([
            'data' => [
                'grade' => $letterGrade . ' (' . $gradePercentage . '%)',
                'average_grade' => $gradePercentage,
                'letter_grade' => $letterGrade,
                'total_score' => $totalScore,
                'max_possible_score' => $maxPossibleScore
            ]
        ]);
    }

    public function courseSubmissions($courseId)
    {
        $user = Auth::user();
        $submissions = Submission::where('user_id', $user->id)
            ->whereHas('moduleItem.module', function ($query) use ($courseId) {
                $query->where('course_id', $courseId);
            })
            ->with(['moduleItem'])
            ->get();

        return response()->json(['data' => $submissions]);
    }

    public function itemDetails($courseId, $itemId)
    {
        $user = Auth::user();
        $item = ModuleItem::whereHas('module', function ($query) use ($courseId) {
            $query->where('course_id', $courseId);
        })
        ->with(['module', 'submissions' => function ($query) use ($user) {
            $query->where('user_id', $user->id);
        }])
        ->findOrFail($itemId);

        return response()->json(['data' => $item]);
    }

    public function submitItem(Request $request, $courseId, $itemId)
    {
        $user = Auth::user();
        $item = ModuleItem::whereHas('module', function ($query) use ($courseId) {
            $query->where('course_id', $courseId);
        })->findOrFail($itemId);

        $submission = Submission::create([
            'user_id' => $user->id,
            'module_item_id' => $itemId,
            'content' => $request->input('content'),
            'status' => 'submitted',
            'submitted_at' => now()
        ]);

        $this->activityService->logSubmission($user, $submission);

        return response()->json([
            'success' => true,
            'data' => $submission
        ]);
    }

    public function markItemComplete($courseId, $itemId)
    {
        $user = Auth::user();
        $item = ModuleItem::whereHas('module', function ($query) use ($courseId) {
            $query->where('course_id', $courseId);
        })->findOrFail($itemId);

        $progress = Progress::updateOrCreate(
            [
                'user_id' => $user->id,
                'module_item_id' => $itemId
            ],
            [
                'status' => 'completed',
                'completed_at' => now()
            ]
        );

        $this->activityService->logItemCompletion($user, $item);

        // Calculate and log course progress
        $course = $item->module->course;
        $totalItems = ModuleItem::whereHas('module', function ($query) use ($course) {
            $query->where('course_id', $course->id);
        })->count();

        $completedItems = Progress::where('user_id', $user->id)
            ->whereHas('moduleItem.module', function ($query) use ($course) {
                $query->where('course_id', $course->id);
            })
            ->where('status', 'completed')
            ->count();

        $progressPercentage = $totalItems > 0 ? round(($completedItems / $totalItems) * 100) : 0;
        $this->activityService->logCourseProgress($user, $course, $progressPercentage);

        return response()->json([
            'success' => true,
            'data' => $progress
        ]);
    }

    public function progress()
    {
        $user = Auth::user();
        $progress = Progress::where('user_id', $user->id)
            ->with(['moduleItem.module.course'])
            ->get()
            ->groupBy('moduleItem.module.course_id')
            ->map(function ($items) {
                $totalItems = $items->count();
                $completedItems = $items->where('status', 'completed')->count();
                return [
                    'course_id' => $items->first()->moduleItem->module->course_id,
                    'progress_percentage' => $totalItems > 0 ? round(($completedItems / $totalItems) * 100) : 0,
                    'items' => $items
                ];
            });

        return response()->json(['data' => $progress->values()]);
    }

    private function calculateLetterGrade($percentage)
    {
        if ($percentage >= 90) return 'A';
        if ($percentage >= 80) return 'B';
        if ($percentage >= 70) return 'C';
        if ($percentage >= 60) return 'D';
        return 'F';
    }
} 