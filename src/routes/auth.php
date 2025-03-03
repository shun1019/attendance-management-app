<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Fortify;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;

Fortify::loginView(function () {
    return request()->is('admin/*') ? view('admin.auth.login') : view('auth.login');
});

Fortify::redirects('login', function () {
    $user = Auth::user();
    if ($user) {
        return $user->role === 1
        ? route('admin.attendance.index')
        : route('attendance.index');
    }

    return route('login');
});

Fortify::authenticateUsing(function (Request $request) {
    $user = User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        throw ValidationException::withMessages([
            'email' => ['ログイン情報が登録されていません。'],
        ]);
    }

    if (request()->is('admin/*') && $user->role !== 1) {
        throw ValidationException::withMessages([
            'email' => ['管理者権限がありません。'],
        ]);
    }

    if (!request()->is('admin/*') && $user->role !== 0) {
        throw ValidationException::withMessages([
            'email' => ['一般ユーザーとしてログインできません。'],
        ]);
    }

    return $user;
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
Route::post('/admin/logout', [AuthenticatedSessionController::class, 'destroy'])->name('admin.logout');
