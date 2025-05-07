<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Course;
use App\Models\Assignment;
use App\Models\Submission;
use App\Models\User;
use Carbon\Carbon;

class InstructorController extends Controller
{
    /**
     * Return all dashboard data for the authenticated instructor.
     */
    public function dashboard(Request $request)
    {
        /** @var \App\Models\User $instructor */
        $instructor = Auth::user();

        // Profile
        $profile = [
            'id' => $instructor->id,
            'name' => $instructor->name,
            'email' => $instructor->email,
            'bio' => $instructor->bio,
            'phone_number' => $instructor->phone_number,
            'profile_picture' => $instructor->profile_picture,
            'role' => $instructor->role,
        ];

        // Get instructor's courses
        $courses = Course::where('instructor_id', $instructor->id)
            ->with(['students', 'assignments'])
            ->get()
            ->map(function ($course) {
                return [
                    'id' => $course->id,
                    'title' => $course->title,
                    'slug' => $course->slug,
                    'start_date' => $course->start_date,
                    'end_date' => $course->end_date,
                    'is_published' => $course->is_published,
                    'student_count' => $course->students->count(),
                    'assignment_count' => $course->assignments->count(),
                ];
            });

        // Recent assignments (last 5)
        $recent_assignments = Assignment::whereIn('course_id', $courses->pluck('id'))
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get(['id', 'title', 'course_id', 'due_date', 'created_at']);

        // Pending submissions (ungraded)
        $pending_submissions = Submission::whereIn('assignment_id', Assignment::whereIn('course_id', $courses->pluck('id'))->pluck('id'))
            ->whereNull('grade')
            ->with(['assignment', 'user'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->map(function ($submission) {
                return [
                    'id' => $submission->id,
                    'assignment_title' => $submission->assignment->title,
                    'course_id' => $submission->assignment->course_id,
                    'student_name' => $submission->user->name,
                    'submitted_at' => $submission->created_at,
                ];
            });

        // Notifications (stub, assuming notifications table/model exists)
        $notifications = []; // Replace with actual query if Notification model exists

        // Calendar Data (assignments and course start/end dates)
        $calendar = [
            'assignments' => Assignment::whereIn('course_id', $courses->pluck('id'))
                ->get(['id', 'title', 'course_id', 'due_date']),
            'courses' => $courses->map(function ($course) {
                return [
                    'id' => $course['id'],
                    'title' => $course['title'],
                    'start_date' => $course['start_date'],
                    'end_date' => $course['end_date'],
                ];
            }),
        ];

        return response()->json([
            'profile' => $profile,
            'courses' => $courses,
            'recent_assignments' => $recent_assignments,
            'pending_submissions' => $pending_submissions,
            'notifications' => $notifications,
            'calendar' => $calendar,
        ]);
    }
} 