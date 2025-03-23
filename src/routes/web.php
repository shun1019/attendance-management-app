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
        Route::post('/{id}/request', [AttendanceController::class, 'requestUpdate'])->where('id', '[0-9]+')->name('request');
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
            Route::get('/staff/{id}', [AdminAttendanceController::class, 'listByStaff'])->where('id', '[0-9]+')->name('staff');
        });

        Route::prefix('staff')->name('staff.')->group(function () {
            Route::get('/list', [AdminStaffController::class, 'index'])->name('index');
            Route::get('/{id}', [AdminStaffController::class, 'show'])->where('id', '[0-9]+')->name('show');
            Route::get('/{id}/export', [AdminStaffController::class, 'exportCsv'])
                ->where('id', '[0-9]+')
                ->name('export');
        });
    });
});

// 共通パスのルート - 管理者・一般ユーザー共通
Route::middleware(['auth'])->group(function () {
    Route::get('/attendance/{id}', [AttendanceController::class, 'show'])
        ->where('id', '[0-9]+')
        ->name('attendance.show');

    Route::put('/attendance/{id}', [AdminAttendanceController::class, 'update'])
        ->where('id', '[0-9]+')
        ->name('attendance.update')
        ->middleware('admin');

    Route::prefix('stamp_correction_request')->name('stamp_correction_request.')->group(function () {
        Route::get('/list', [AttendanceRequestController::class, 'index'])
            ->name('list');
        Route::get('/{id}', [AttendanceRequestController::class, 'show'])
            ->where('id', '[0-9]+')
            ->name('show');
        Route::get('/approve/{attendance_correct_request}', [AdminRequestController::class, 'showApproveForm'])
            ->where('attendance_correct_request', '[0-9]+')
            ->name('approve.form')
            ->middleware('admin');

        Route::post('/approve/{attendance_correct_request}', [AdminRequestController::class, 'approve'])
            ->where('attendance_correct_request', '[0-9]+')
            ->name('approve')
            ->middleware('admin');
    });
});
