<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\EnrollmentController;
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
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\GradeController;
use Illuminate\Http\Request;
use App\Services\JwtService;

// ── Public endpoints ────────────────────────────────────────────────────────────
Route::get('/ping', function () {
    return response()->json(['pong' => true]);
});

Route::get('/welcome', function () {
    return response()->json(['message' => 'Welcome to the API']);
});

// Authentication routes
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// Token verification
Route::get('/verify-token', function (Request $request) {
    try {
        $token = $request->cookie('jwt_token');
        
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
Route::middleware(['api', \App\Http\Middleware\JwtMiddleware::class])->group(function () {
    // Authentication
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [UserController::class, 'me']);

    // Student routes
    Route::prefix('student')->group(function () {
        // Dashboard
        Route::get('dashboard', [StudentController::class, 'dashboard']);
        
        // Courses
        Route::get('courses', [StudentController::class, 'courses']);
        Route::get('courses/{course}', [StudentController::class, 'courseDetails']);
        Route::get('courses/{course}/modules', [StudentController::class, 'courseModules']);
        Route::get('courses/{course}/progress', [StudentController::class, 'courseProgress']);
        Route::get('courses/{course}/statistics', [StudentController::class, 'courseStatistics']);
        Route::get('courses/{course}/submissions', [StudentController::class, 'courseSubmissions']);
        
        // Course Items
        Route::get('courses/{course}/items/{item}', [StudentController::class, 'itemDetails']);
        Route::post('courses/{course}/items/{item}/submit', [StudentController::class, 'submitItem']);
        Route::post('courses/{course}/items/{item}/complete', [StudentController::class, 'markItemComplete']);
        
        // Progress
        Route::get('progress', [StudentController::class, 'progress']);
    });

    // Dashboard routes
    Route::prefix('dashboard')->group(function () {
        Route::get('student', [StudentController::class, 'dashboard']);
        Route::get('instructor', [InstructorController::class, 'dashboard']);
    });

    // Course routes
    Route::get('/courses', [CourseController::class, 'index']);
    Route::post('/courses', [CourseController::class, 'store']);
    Route::get('/courses/{course}', [CourseController::class, 'show']);
    Route::put('/courses/{course}', [CourseController::class, 'update']);
    Route::delete('/courses/{course}', [CourseController::class, 'destroy']);
    Route::get('/courses/{course}/modules', [CourseController::class, 'modules']);
    Route::post('/courses/{course}/modules', [ModuleController::class, 'store']);
    Route::get('/courses/{course}/module-items', [CourseController::class, 'moduleItems']);
    Route::get('/courses/{course}/submittable-items', [ModuleItemController::class, 'submittableItems']);
    Route::post('/courses/{course}/modules/{module}/items', [ModuleItemController::class, 'store']);
    Route::get('/courses/{course}/progress', [CourseController::class, 'progress']);
    Route::get('/courses/{course}/statistics', [CourseController::class, 'statistics']);

    // Enrollment routes
    Route::get('my-courses', [EnrollmentController::class, 'index']);
    Route::get('courses/{course}/enrollments', [EnrollmentController::class, 'courseStudents']);
    Route::post('courses/{course}/enroll', [EnrollmentController::class, 'store']);
    Route::delete('courses/{course}/unenroll', [EnrollmentController::class, 'destroy']);

    // Module routes
    Route::get('/modules', [ModuleController::class, 'index']);
    Route::post('/modules', [ModuleController::class, 'store']);
    Route::get('/modules/{module}', [ModuleController::class, 'show']);
    Route::put('/modules/{module}', [ModuleController::class, 'update']);
    Route::delete('/modules/{module}', [ModuleController::class, 'destroy']);
    Route::get('/modules/{module}/items', [ModuleController::class, 'items']);
    Route::get('/modules/{module}/progress', [ModuleController::class, 'progress']);

    // Module Item routes
    Route::get('/module-items', [ModuleItemController::class, 'index']);
    Route::post('/module-items', [ModuleItemController::class, 'store']);
    Route::get('/module-items/{moduleItem}', [ModuleItemController::class, 'show']);
    Route::put('/module-items/{moduleItem}', [ModuleItemController::class, 'update']);
    Route::delete('/module-items/{moduleItem}', [ModuleItemController::class, 'destroy']);
    Route::get('/module-items/{moduleItem}/submissions', [ModuleItemController::class, 'submissions']);

    // Submission routes
    Route::get('/submissions', [SubmissionController::class, 'index']);
    Route::post('/submissions', [SubmissionController::class, 'store']);
    Route::get('/submissions/{submission}', [SubmissionController::class, 'show']);
    Route::put('/submissions/{submission}', [SubmissionController::class, 'update']);
    Route::post('/submissions/{submission}/grade', [SubmissionController::class, 'grade']);
    Route::get('/user/submissions', [SubmissionController::class, 'userSubmissions']);

    // Progress routes
    Route::get('/progress/my-progress', [ProgressController::class, 'myProgress']);
    Route::get('/user/progress', [ProgressController::class, 'userProgress']);
    Route::post('/progress', [ProgressController::class, 'store']);
    Route::put('/progress/{progress}', [ProgressController::class, 'update']);
    Route::post('/progress/bulk-update', [ProgressController::class, 'bulkUpdate']);

    // Question routes
    Route::apiResource('module-items.questions', QuestionController::class)->shallow();

    // Option routes
    Route::apiResource('module-items.questions.options', OptionController::class)->shallow();

    // Answer routes
    Route::apiResource('module-items.questions.answers', AnswerController::class)->shallow();

    // Grade routes
    Route::get('grades/{grade}', [GradeController::class, 'show']);
    Route::put('grades/{grade}', [GradeController::class, 'update']);
    Route::delete('grades/{grade}', [GradeController::class, 'destroy']);
    Route::get('students/{student}/grades', [GradeController::class, 'studentGrades']);

    // Notification routes
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('unread', [NotificationController::class, 'unread']);
        Route::post('{id}/read', [NotificationController::class, 'markAsRead']);
        Route::post('read-all', [NotificationController::class, 'markAllAsRead']);
        Route::delete('{id}', [NotificationController::class, 'destroy']);
    });

    // Announcement routes
    Route::put('announcements/{announcement}', [AnnouncementController::class, 'update']);
    Route::delete('announcements/{announcement}', [AnnouncementController::class, 'destroy']);
});
