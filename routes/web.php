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

Route::get('/', function () {
    return view('app');
});

Route::get('/recordings', function () {
    return view('app');
});

Route::get('/profile', function () {
    return view('app');
});

Route::get('/system-info', function () {
    return view('app');
});

Route::get('/system-info/storage', function () {
    return view('app');
});

Route::get('/system-info/activity', function () {
    return view('app');
});

Route::get('/system-info/sessions', function () {
    return view('app');
});

// Authentication routes
Route::post('/api/register', [AuthController::class, 'register'])->middleware('web');
Route::post('/api/login', [AuthController::class, 'login'])->middleware('web');
Route::post('/api/logout', [AuthController::class, 'logout'])->middleware('auth:web');
Route::get('/api/user', [AuthController::class, 'user'])->middleware('auth:web');
Route::get('/api/user/stats', [UserController::class, 'stats'])->middleware('auth:web');
Route::get('/api/system/stats', [UserController::class, 'systemStats'])->middleware('auth:web');

// Detailed report routes (admin)
Route::get('/api/system/users-storage', [UserController::class, 'usersStorage'])->middleware('auth:web');
Route::get('/api/system/users-activity', [UserController::class, 'usersActivity'])->middleware('auth:web');
Route::get('/api/system/users-sessions', [UserController::class, 'usersSessions'])->middleware('auth:web');

// User CRUD routes (protected)
Route::middleware('auth:web')->group(function () {
    Route::apiResource('api/users', UserController::class);
});

// Recording routes
Route::get('/api/recordings', [RecordingController::class, 'index']);
Route::get('/api/recordings/{recording}', [RecordingController::class, 'show']);
Route::get('/api/recordings/{recording}/stream', [RecordingController::class, 'stream']);

// Recording types routes
Route::get('/api/recording-types', [RecordingTypeController::class, 'index']);

Route::middleware('auth:web')->group(function () {
    Route::post('/api/recordings', [RecordingController::class, 'store']);
    Route::put('/api/recordings/{recording}', [RecordingController::class, 'update']);
    Route::delete('/api/recordings/{recording}', [RecordingController::class, 'destroy']);
    
    // Recording session routes (all require authentication)
    Route::get('/api/recording-sessions', [RecordingSessionController::class, 'index']);
    Route::get('/api/recording-sessions/{session}', [RecordingSessionController::class, 'show']);
    Route::post('/api/recording-sessions', [RecordingSessionController::class, 'store']);
    Route::put('/api/recording-sessions/{session}', [RecordingSessionController::class, 'update']);
    Route::delete('/api/recording-sessions/{session}', [RecordingSessionController::class, 'destroy']);
});

// API routes for the Vue app can be added here
Route::get('/api/stats', function () {
    return response()->json([
        'users' => rand(1200, 1300),
        'projects' => rand(320, 360),
        'uptime' => 99.9
    ]);
});

Route::get('/api/plans', [PlanController::class, 'index'])->middleware('auth:web');

// Update user plan
Route::post('/api/user/plan', [UserController::class, 'updatePlan'])->middleware('auth:web');

Route::get('/api/features', function () {
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

Route::middleware('auth:sanctum')->post('/api/stripe/payment-intent', [StripeController::class, 'createPaymentIntent']);

// Route::middleware(['auth:sanctum','can:manage_users'])->group(function () {
    Route::get('api/admin/users', [UserController::class, 'index']);
// });

Route::get('api/roles', [RoleController::class, 'index']);

Route::delete('/api/admin/users/{id}', [App\Http\Controllers\UserController::class, 'destroy']);
