<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

// Route::middleware(['auth:web','can:manage_users'])->group(function () {
    Route::get('admin/users', [UserController::class, 'index']);
// });
