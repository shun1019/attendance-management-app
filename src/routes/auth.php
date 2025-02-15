<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Fortify;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

// Fortifyの認証画面設定
Fortify::loginView(fn() => view('auth.login'));
Fortify::registerView(fn() => view('auth.register'));

// Fortify::verifyEmailView(fn() => view('auth.verify-email'));

Fortify::redirects('register', fn() => route('login'));

Fortify::authenticateUsing(function (Request $request) {
    $user = \App\Models\User::where('email', $request->email)->first();

    if ($user && \Hash::check($request->password, $user->password)) {
        return $user;
    }

    return null;
});

/*
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect('/attendance')->with('success', 'メール認証が完了しました！');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('message', '認証メールを再送信しました。');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');
*/

Route::post('/logout', function () {
    auth()->logout();
    return redirect('/login');
})->name('logout');
