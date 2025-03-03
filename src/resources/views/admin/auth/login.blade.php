@extends('layouts.auth')

@section('title', '管理者ログイン')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/login.css') }}">
@endsection

@section('content')
<div class="login-form">
    <h1>管理者ログイン</h1>

    <form action="{{ route('admin.login') }}" method="POST">
        @csrf
        <div class="login-form__group">
            <label for="email">メールアドレス</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}">
            @error('email')
            <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <div class="login-form__group">
            <label for="password">パスワード</label>
            <input type="password" name="password" id="password">
            @error('password')
            <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <button class="login-btn" type="submit">ログインする</button>
    </form>

</div>
@endsection