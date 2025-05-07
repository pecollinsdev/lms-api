<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Course;
use App\Models\Assignment;
use App\Models\Submission;
use App\Models\Progress;
use App\Models\User;
use Carbon\Carbon;

class StudentController extends Controller
{
    /**
     * Return all dashboard data for the authenticated student.
     */
    public function dashboard(Request $request)
    {
        /** @var \App\Models\User $student */
        $student = Auth::user();

        // Profile
        $profile = [
            'id' => $student->id,
            'name' => $student->name,
            'email' => $student->email,
            'bio' => $student->bio,
            'phone_number' => $student->phone_number,
            'profile_picture' => $student->profile_picture,
            'role' => $student->role,
        ];

        // Get enrolled courses collection
        $enrolledCourses = $student->enrolledCourses()->with(['instructor', 'assignments'])->get();

        // Enrolled Courses with stats
        $courses = $enrolledCourses->map(function ($course) use ($student) {
            // Calculate current grade for this course
            $submissions = Submission::where('user_id', $student->id)
                ->whereIn('assignment_id', $course->assignments->pluck('id'))
                ->whereNotNull('grade')
                ->get();
            $total = $submissions->sum('grade');
            $count = $submissions->count();
            $current_grade = $count > 0 ? round($total / $count, 2) : null;
            return [
                'id' => $course->id,
                'title' => $course->title,
                'slug' => $course->slug,
                'instructor' => $course->instructor ? $course->instructor->name : null,
                'start_date' => $course->start_date,
                'end_date' => $course->end_date,
                'is_published' => $course->is_published,
                'current_grade' => $current_grade,
            ];
        });

        // Upcoming Deadlines (next 2 weeks)
        $enrolledCourseIds = $enrolledCourses->pluck('id');
        $upcoming_deadlines = Assignment::whereIn('course_id', $enrolledCourseIds)
            ->where('due_date', '>=', Carbon::now())
            ->where('due_date', '<=', Carbon::now()->addWeeks(2))
            ->orderBy('due_date')
            ->get(['id', 'title', 'course_id', 'due_date']);

        // Recent Announcements (stub, assuming announcements table/model exists)
        $recent_announcements = []; // Replace with actual query if Announcement model exists

        // Notifications (stub, assuming notifications table/model exists)
        $notifications = []; // Replace with actual query if Notification model exists

        // Current GPA (average of all graded submissions)
        $all_graded = Submission::where('user_id', $student->id)->whereNotNull('grade')->get();
        $gpa = $all_graded->count() > 0 ? round($all_graded->avg('grade'), 2) : null;

        // Calendar Data (assignments and course start/end dates)
        $calendar = [
            'assignments' => Assignment::whereIn('course_id', $enrolledCourseIds)
                ->get(['id', 'title', 'course_id', 'due_date']),
            'courses' => $enrolledCourses->map(function ($course) {
                return [
                    'id' => $course->id,
                    'title' => $course->title,
                    'start_date' => $course->start_date,
                    'end_date' => $course->end_date,
                ];
            }),
        ];

        return response()->json([
            'profile' => $profile,
            'courses' => $courses,
            'upcoming_deadlines' => $upcoming_deadlines,
            'recent_announcements' => $recent_announcements,
            'notifications' => $notifications,
            'gpa' => $gpa,
            'calendar' => $calendar,
        ]);
    }
} 