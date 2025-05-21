<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Course;
use App\Models\Assignment;
use App\Models\Submission;
use App\Models\User;
use Carbon\Carbon;
use App\Models\ModuleItem;

class InstructorController extends Controller
{
    /**
     * Return all dashboard data for the authenticated instructor.
     */
    public function dashboard()
    {
        $user = request()->user();
        
        // Get instructor's courses
        $courses = Course::where('instructor_id', $user->id)
            ->with(['students', 'modules.moduleItems'])
            ->get();

        // Course statistics
        $course_stats = $courses->map(function ($course) {
            return [
                'id' => $course->id,
                'title' => $course->title,
                'student_count' => $course->students->count(),
                'module_item_count' => $course->modules->pluck('moduleItems')->flatten()->where('type', 'assignment')->count(),
            ];
        });

        // Recent module items (last 5)
        $recent_module_items = ModuleItem::whereIn('module_id', $courses->pluck('modules')->flatten()->pluck('id'))
            ->where('type', 'assignment')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Pending submissions
        $pending_submissions = Submission::whereIn('module_item_id', $recent_module_items->pluck('id'))
            ->where('status', 'pending')
            ->with(['moduleItem', 'user'])
            ->get()
            ->map(function ($submission) {
                return [
                    'id' => $submission->id,
                    'module_item_title' => $submission->moduleItem->title,
                    'course_id' => $submission->moduleItem->module->course_id,
                    'student_name' => $submission->user->name,
                    'submitted_at' => $submission->submitted_at,
                ];
            });

        // Calendar Data (module items and course start/end dates)
        $calendar_data = [
            'module_items' => ModuleItem::whereIn('module_id', $courses->pluck('modules')->flatten()->pluck('id'))
                ->where('type', 'assignment')
                ->whereNotNull('due_date')
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'title' => $item->title,
                        'due_date' => $item->due_date,
                        'course_id' => $item->module->course_id,
                    ];
                }),
            'courses' => $courses->map(function ($course) {
                return [
                    'id' => $course->id,
                    'title' => $course->title,
                    'start_date' => $course->start_date,
                    'end_date' => $course->end_date,
                ];
            }),
        ];

        return $this->respond([
            'course_stats' => $course_stats,
            'recent_module_items' => $recent_module_items,
            'pending_submissions' => $pending_submissions,
            'calendar_data' => $calendar_data,
        ]);
    }
} 