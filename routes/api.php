<?php

use App\Http\Controllers\EmployeeAttendanceController;
use Illuminate\Support\Facades\Route;


// Route::middleware('auth:sanctum')->group(function () {
    Route::post('/attendance/clock-in', [EmployeeAttendanceController::class, 'clockIn']);
    Route::post('/attendance/clock-out', [EmployeeAttendanceController::class, 'clockOut']);
    Route::get('/attendance/status', [EmployeeAttendanceController::class, 'getClockInStatus']);
// });

