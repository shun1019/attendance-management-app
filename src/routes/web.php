<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\StampCorrectionRequestController;
use App\Http\Controllers\Admin\AdminAttendanceController;
use App\Http\Controllers\Admin\AdminRequestController;
use App\Http\Controllers\Admin\AdminStaffController;

Route::get('/', function () {
    return redirect('/login');
});

require __DIR__ . '/auth.php';

// 一般ユーザー向け
Route::middleware(['auth'])->group(function () {
    Route::prefix('attendance')->name('attendance.')->group(function () {
        Route::get('/', [AttendanceController::class, 'index'])->name('index');
        Route::post('/start', [AttendanceController::class, 'start'])->name('start');
        Route::post('/end', [AttendanceController::class, 'end'])->name('end');
        Route::post('/break/start', [AttendanceController::class, 'breakStart'])->name('break.start');
        Route::post('/break/end', [AttendanceController::class, 'breakEnd'])->name('break.end');
        Route::get('/list', [AttendanceController::class, 'list'])->name('list');
        Route::get('/{id}', [AttendanceController::class, 'show'])->where('id', '[0-9]+')->name('show');
        Route::post('/{id}/request', [AttendanceController::class, 'requestUpdate'])->where('id', '[0-9]+')->name('request');
    });

    // 勤怠修正申請
    Route::prefix('stamp_correction_request')->name('stamp_correction_request.')->group(function () {
        Route::get('/list', [StampCorrectionRequestController::class, 'index'])->name('list');
        Route::get('/{id}', [StampCorrectionRequestController::class, 'show'])->where('id', '[0-9]+')->name('show');
    });
});

// 管理者向け
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::prefix('attendance')->name('attendance.')->group(function () {
        Route::get('/list', [AdminAttendanceController::class, 'index'])->name('index');
        Route::get('/{id}', [AdminAttendanceController::class, 'show'])->where('id', '[0-9]+')->name('show');
        Route::get('/staff/{id}', [AdminAttendanceController::class, 'listByStaff'])->where('id', '[0-9]+')->name('staff');
    });

    Route::prefix('staff')->name('staff.')->group(function () {
        Route::get('/list', [AdminStaffController::class, 'index'])->name('index');
    });

    Route::prefix('requests')->name('request.')->group(function () {
        Route::get('/', [AdminRequestController::class, 'index'])->name('index');
        Route::get('/approve/{id}', [AdminRequestController::class, 'approve'])->where('id', '[0-9]+')->name('approve');
    });
});
