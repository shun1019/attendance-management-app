@extends('layouts.auth')

@section('title', 'メール認証のお願い')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/verify-email.css') }}">
@endsection

@section('content')
<div class="verify-email_container">
    <p>登録していただいたメールアドレスに認証メールを送付しました。</p>
    <p>メール認証を完了してください。</p>

    <form action="http://localhost:8025" method="get">
        <button type="submit" class="certification-btn">認証はこちらから</button>
    </form>
    @csrf
    <a href="{{ route('verification.send') }}" class="verification-send">認証メールを再送する</a>
</div>
@endsection