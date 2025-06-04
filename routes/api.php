<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Task\TaskController;
use App\Http\Controllers\Api\User\ProfileController;
use App\Http\Controllers\Api\PasswordResetController;

Route::middleware('throttle:5,1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [PasswordResetController::class, 'forgot']);
    Route::post('/reset-password', [PasswordResetController::class, 'reset']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::prefix('user')->group(function(){
        Route::get('/profile', [ProfileController::class, 'me']);
        Route::put('/profile/update', [ProfileController::class, 'update']);
        Route::post('/profile/change-password', [ProfileController::class, 'changePassword']);
    });

    Route::prefix('tasks')->group(function(){
        Route::get('/list', [TaskController::class, 'index']);
        Route::post('/create', [TaskController::class, 'store']);
        Route::get('/{task}/show', [TaskController::class, 'show']);
        Route::put('/{task}/update', [TaskController::class, 'update']);
        Route::delete('/{task}/delete', [TaskController::class, 'destroy']);
        Route::put('/{task}/update-status', [TaskController::class, 'updateStatus']);
    });
});
