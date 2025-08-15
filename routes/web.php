<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RecordingController;

Route::get('/', function () {
    return view('app');
});

Route::get('/recordings', function () {
    return view('app');
});

// Authentication routes
Route::post('/api/register', [AuthController::class, 'register']);
Route::post('/api/login', [AuthController::class, 'login']);
Route::post('/api/logout', [AuthController::class, 'logout'])->middleware('auth:web');
Route::get('/api/user', [AuthController::class, 'user'])->middleware('auth:web');

// User CRUD routes (protected)
Route::middleware('auth:web')->group(function () {
    Route::apiResource('api/users', UserController::class);
});

// Recording routes
Route::get('/api/recordings', [RecordingController::class, 'index']);
Route::get('/api/recordings/{recording}', [RecordingController::class, 'show']);
Route::get('/api/recordings/{recording}/stream', [RecordingController::class, 'stream']);

Route::middleware('auth:web')->group(function () {
    Route::post('/api/recordings', [RecordingController::class, 'store']);
    Route::put('/api/recordings/{recording}', [RecordingController::class, 'update']);
    Route::delete('/api/recordings/{recording}', [RecordingController::class, 'destroy']);
});

// API routes for the Vue app can be added here
Route::get('/api/stats', function () {
    return response()->json([
        'users' => rand(1200, 1300),
        'projects' => rand(320, 360),
        'uptime' => 99.9
    ]);
});

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
