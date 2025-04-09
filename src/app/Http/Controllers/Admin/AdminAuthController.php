<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\LoginRequest;

class AdminAuthController extends Controller
{
    /**
     * 管理者ログインフォームを表示
     */
    public function showLoginForm()
    {
        return view('admin.auth.login');
    }

    /**
     * 管理者ログイン処理
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();
        $credentials['role'] = 1;

        if (Auth::attempt($credentials)) {
            return redirect()->route('admin.attendance.index');
        }

        return back()->withErrors([
            'email' => 'ログイン情報が登録されていません',
        ]);
    }

    /**
     * 管理者ログアウト処理
     */
    public function logout()
    {
        Auth::logout();
        return redirect()->route('admin.login');
    }
}
