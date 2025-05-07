<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\OptionController;
use App\Http\Controllers\AnswerController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\ProgressController;

// Simple test route
Route::get('/ping', function () {
    return response()->json(['pong' => true]);
});

// Test route
Route::get('/test-route', function () {
    return response()->json(['message' => 'Laravel routing is working!']);
});

// ── Public endpoints ────────────────────────────────────────────────────────────
// No JWT required
Route::get('/welcome', function () {
    return response()->json([
        'message' => 'Welcome to the API'
    ]);
});
Route::post('register', [AuthController::class, 'register']);
Route::post('login',    [AuthController::class, 'login']);

// ── Protected endpoints ─────────────────────────────────────────────────────────
// Requires a valid token via the auth.jwt middleware
Route::middleware(['api', 'auth.jwt'])->group(function () {
    // Authentication
    Route::post('logout', [AuthController::class, 'logout']);

    // Courses (CRUD)
    Route::apiResource('courses', CourseController::class);

    // Enrollments
    // List my courses (student's view)
    Route::get('my-courses', [EnrollmentController::class, 'index']);

    // List all students in a course (instructor/admin view)
    Route::get('courses/{course}/enrollments', [EnrollmentController::class, 'courseStudents']);

    // Enroll and Unenroll
    Route::post('courses/{course}/enroll',     [EnrollmentController::class, 'store']);
    Route::delete('courses/{course}/unenroll', [EnrollmentController::class, 'destroy']);

    // Assignments
    Route::apiResource('courses.assignments', AssignmentController::class)->shallow();

    // Questions
    Route::apiResource('assignments.questions', QuestionController::class)->shallow();

    // Options
    Route::apiResource('assignments.questions.options', OptionController::class)->shallow();

    // Answers
    Route::apiResource('assignments.questions.answers', AnswerController::class)->shallow();

    // Submissions
    Route::apiResource('assignments.submissions', SubmissionController::class)->shallow();
    Route::get('my-submissions', [SubmissionController::class, 'mySubmissions']);
    Route::patch('submissions/{submission}', [SubmissionController::class, 'update']);

    // Progress
    Route::get('/my-progress',               [ProgressController::class, 'myProgress']);
    Route::post('/assignments/{assignment}/progress',  [ProgressController::class, 'store']);
    Route::get('/assignments/{assignment}/progress',   [ProgressController::class, 'index']);
    Route::get('/progress/{progress}',       [ProgressController::class, 'show']);
    Route::patch('/progress/{progress}',     [ProgressController::class, 'update']);
    Route::delete('/progress/{progress}',    [ProgressController::class, 'destroy']);
});
