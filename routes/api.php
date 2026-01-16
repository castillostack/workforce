<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\ReportingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Auth routes (sin auth)
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register'])->middleware(['auth:sanctum', 'role:admin']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// User routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::get('/profile', [UserController::class, 'profile']);
    Route::put('/profile', [UserController::class, 'updateProfile']);
    Route::post('/change-password', [UserController::class, 'changePassword']);
});

// Import routes
Route::middleware(['auth:sanctum', 'role:supervisor'])->group(function () {
    Route::post('/import/{type}', [ImportController::class, 'import']);
});

// Schedule routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/schedule/{employeeId}', [ScheduleController::class, 'getSchedule']);
    Route::post('/schedule/{employeeId}', [ScheduleController::class, 'createSchedule'])->middleware('role:supervisor');
    Route::post('/schedule/swap', [ScheduleController::class, 'requestSwap']);
    Route::post('/schedule/swap/{requestId}/decide', [ScheduleController::class, 'decideSwap'])->middleware('role:supervisor');
});

// Reporting routes
Route::middleware(['auth:sanctum', 'role:analyst'])->group(function () {
    Route::get('/report/adherence/{employeeId}', [ReportingController::class, 'adherenceReport']);
    Route::get('/report/metrics', [ReportingController::class, 'dailyMetrics']);
});