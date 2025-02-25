<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Fortify;
use Illuminate\Http\Request;
use App\Models\User;

// 🔹 一般ユーザーのログイン・登録画面
Fortify::loginView(fn() => view('auth.login'));
Fortify::registerView(fn() => view('auth.register'));

// 🔹 Fortify のリダイレクト設定
Fortify::redirects('register', fn() => route('login'));

// 🔹 一般ユーザーの認証処理
Fortify::authenticateUsing(function (Request $request) {
    $user = User::where('email', $request->email)->where('role', 0)->first();

    if ($user && Hash::check($request->password, $user->password)) {
        return $user;
    }

    return null;
});

// 🔹 ログアウト処理（一般ユーザー）
Route::post('/logout', function () {
    auth()->logout();
    return redirect('/login');
})->name('logout');
