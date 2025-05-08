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
use App\Http\Controllers\UserController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\ModuleItemController;
use App\Http\Controllers\NotificationController;
use Illuminate\Http\Request;
use App\Services\JwtService;

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

// Test route for token verification
Route::get('/verify-token', function (Request $request) {
    try {
        // First try to get token from cookie
        $token = $request->cookie('jwt_token');
        
        // If not in cookie, try Authorization header
        if (!$token) {
            $header = $request->header('Authorization', '');
            if (preg_match('/Bearer\s(\S+)/', $header, $matches)) {
                $token = $matches[1];
            }
        }

        if (!$token) {
            return response()->json(['error' => 'No token provided'], 401);
        }
        
        $jwt = app(JwtService::class);
        $decoded = $jwt->validateToken($token);
        
        return response()->json([
            'valid' => true,
            'payload' => $decoded,
            'expires_in' => $decoded->exp - time() . ' seconds'
        ]);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Invalid token: ' . $e->getMessage()], 401);
    }
});

// ── Protected endpoints ─────────────────────────────────────────────────────────
// Requires a valid token via the JWT middleware
Route::middleware(['api', \App\Http\Middleware\JwtMiddleware::class])->group(function () {
    // Authentication
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [UserController::class, 'me']);

    // Student Dashboard
    Route::get('student/dashboard', [StudentController::class, 'dashboard']);

    // Instructor Dashboard
    Route::get('instructor/dashboard', [InstructorController::class, 'dashboard']);

    // Courses (CRUD)
    Route::apiResource('courses', CourseController::class);
    
    // Course-specific endpoints
    Route::get('courses/{course}/students', [CourseController::class, 'students']);
    Route::get('courses/{course}/assignments', [CourseController::class, 'assignments']);
    Route::get('courses/{course}/progress', [CourseController::class, 'progress']);
    Route::get('courses/{course}/statistics', [CourseController::class, 'statistics']);

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
    Route::get('courses/{course}/assignments/{assignment}', [AssignmentController::class, 'show']);

    // Modules
    Route::apiResource('courses.modules', ModuleController::class)->shallow();
    Route::get('courses/{course}/modules/{module}', [ModuleController::class, 'show']);

    // Module Items
    Route::apiResource('module-items', ModuleItemController::class);
    Route::post('module-items/{moduleItem}/reorder', [ModuleItemController::class, 'reorder']);

    // Questions
    Route::apiResource('assignments.questions', QuestionController::class)->shallow();

    // Options
    Route::apiResource('assignments.questions.options', OptionController::class)->shallow();

    // Answers
    Route::apiResource('assignments.questions.answers', AnswerController::class)->shallow();

    // Submissions
    Route::get('courses/{course}/assignments/{assignment}/submissions', [SubmissionController::class, 'index']);
    Route::post('courses/{course}/assignments/{assignment}/submissions', [SubmissionController::class, 'store']);
    Route::get('courses/{course}/assignments/{assignment}/submissions/{submission}', [SubmissionController::class, 'show']);
    Route::get('my-submissions', [SubmissionController::class, 'mySubmissions']);
    Route::patch('submissions/{submission}', [SubmissionController::class, 'update']);
    
    // Instructor Assignment Submissions
    Route::get('instructor/courses/{course}/assignments/{assignment}/submissions', [SubmissionController::class, 'index']);

    // Progress
    Route::get('/my-progress',               [ProgressController::class, 'myProgress']);
    Route::post('/assignments/{assignment}/progress',  [ProgressController::class, 'store']);
    Route::get('/assignments/{assignment}/progress',   [ProgressController::class, 'index']);
    Route::get('/progress/{progress}',       [ProgressController::class, 'show']);
    Route::patch('/progress/{progress}',     [ProgressController::class, 'update']);
    Route::delete('/progress/{progress}',    [ProgressController::class, 'destroy']);

    // Notification routes
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread', [NotificationController::class, 'unread']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);
});
