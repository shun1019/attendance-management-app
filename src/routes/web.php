<?php

use Illuminate\Support\Facades\Route;

/**
 * 認証ルート（Laravel Fortify）
 */
Route::group(['middleware' => ['guest']], function () {
    Route::get('/register', function () {
        return view('auth.register');
    })->name('register');

    Route::get('/login', function () {
        return view('auth.login');
    })->name('login');
});

/**
 * ログイン後のページ（ダッシュボード）
 */
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

/**
 * ログアウト
 */
Route::post('/logout', function () {
    auth()->logout();
    return redirect('/login');
})->name('logout');