<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceRequestController;
use App\Http\Controllers\Admin\AdminAuthController;
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

    Route::prefix('stamp_correction_request')->name('stamp_correction_request.')->group(function () {
        Route::get('/list', [AttendanceRequestController::class, 'index'])->name('list');
        Route::get('/{id}', [AttendanceRequestController::class, 'show'])->where('id', '[0-9]+')->name('show');
    });
});

// 管理者向け
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login');
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::prefix('attendance')->name('attendance.')->group(function () {
            Route::get('/list', [AdminAttendanceController::class, 'index'])->name('index');
            Route::get('/{id}', [AdminAttendanceController::class, 'show'])->where('id', '[0-9]+')->name('show');
            Route::put('/{id}', [AdminAttendanceController::class, 'update'])->where('id', '[0-9]+')->name('update');
            Route::get('/staff/{id}', [AdminAttendanceController::class, 'listByStaff'])->where('id', '[0-9]+')->name('staff');
        });

        Route::prefix('staff')->name('staff.')->group(function () {
            Route::get('/list', [AdminStaffController::class, 'index'])->name('index');
            Route::get('/{id}', [AdminStaffController::class, 'show'])->where('id', '[0-9]+')->name('show');
            Route::get('/{id}/attendance/{attendance_id}', [AdminAttendanceController::class, 'attendanceDetail'])
                ->where(['id' => '[0-9]+', 'attendance_id' => '[0-9]+'])
                ->name('attendance');
        });

        Route::prefix('stamp_correction_request')->name('stamp_correction_request.')->group(function () {
            Route::get('/list', [AdminRequestController::class, 'index'])->name('list');
            Route::get('/{id}', [AdminRequestController::class, 'show'])->where('id', '[0-9]+')->name('show');
            Route::post('/approve/{id}', [AdminRequestController::class, 'approve'])->where('id', '[0-9]+')->name('approve');
        });
    });
});
