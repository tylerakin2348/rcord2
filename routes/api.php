


<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RecordingController;
use App\Http\Controllers\RecordingTypeController;
use App\Http\Controllers\RecordingSessionController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\StripeController;

// NOTE: Use auth:sanctum for protected API routes. Do not use web middleware for APIs.
// Public routes do not require authentication.

// Authentication routes (API - Sanctum)
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('user', [AuthController::class, 'user']);
    Route::get('user/stats', [UserController::class, 'stats']);
    Route::get('system/stats', [UserController::class, 'systemStats']);
    // System info routes (locked down by permission)
        Route::middleware(['auth:sanctum', 'can:manage_system_info'])->group(function () {
            Route::get('system/users-storage', [UserController::class, 'usersStorage']);
            Route::get('system/users-activity', [UserController::class, 'usersActivity']);
            Route::get('system/users-sessions', [UserController::class, 'usersSessions']);
        });
    });

    // User CRUD routes (protected)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('user', [AuthController::class, 'user']);
        Route::get('user/stats', [UserController::class, 'stats']);
        Route::apiResource('users', UserController::class);
    });

// User CRUD routes (protected)
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('users', UserController::class);
});

// Recording routes
Route::get('recordings', [RecordingController::class, 'index']);
Route::get('recordings/{recording}', [RecordingController::class, 'show']);
Route::get('recordings/{recording}/stream', [RecordingController::class, 'stream']);

// Recording types routes
Route::get('recording-types', [RecordingTypeController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('recordings', [RecordingController::class, 'store']);
    Route::put('recordings/{recording}', [RecordingController::class, 'update']);
    Route::delete('recordings/{recording}', [RecordingController::class, 'destroy']);
    // Recording session routes (all require authentication)
    Route::get('recording-sessions', [RecordingSessionController::class, 'index']);
    Route::get('recording-sessions/{session}', [RecordingSessionController::class, 'show']);
    Route::post('recording-sessions', [RecordingSessionController::class, 'store']);
    Route::put('recording-sessions/{session}', [RecordingSessionController::class, 'update']);
    Route::delete('recording-sessions/{session}', [RecordingSessionController::class, 'destroy']);
});

// API routes for the Vue app can be added here
Route::get('stats', function () {
    return response()->json([
        'users' => rand(1200, 1300),
        'projects' => rand(320, 360),
        'uptime' => 99.9
    ]);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('plans', [PlanController::class, 'index']);
    // Update user plan
    Route::post('user/plan', [UserController::class, 'updatePlan']);
});

Route::get('features', function () {
    return response()->json([
        [
            'id' => 1,
            'title' => 'Fast & Reliable',
            'description' => 'Built with Vue 3 and Laravel for optimal performance',
            'icon' => '⚡'
        ],
        [
            'id' => 2,
            'title' => 'Modern UI',
            'description' => 'Beautiful, responsive design with Tailwind CSS',
            'icon' => '🎨'
        ],
        [
            'id' => 3,
            'title' => 'Real-time Updates',
            'description' => 'Stay connected with live data synchronization',
            'icon' => '🔄'
        ],
        [
            'id' => 4,
            'title' => 'Secure',
            'description' => 'Enterprise-grade security with Laravel backend',
            'icon' => '🔒'
        ]
    ]);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('stripe/payment-intent', [StripeController::class, 'createPaymentIntent']);
    // Admin routes
    Route::get('admin/users', [UserController::class, 'index']);
    Route::get('roles', [RoleController::class, 'index']);
    Route::delete('admin/users/{id}', [UserController::class, 'destroy']);
});
