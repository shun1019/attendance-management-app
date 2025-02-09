@extends('layouts.auth')

@section('title', 'メール認証のお願い')

@section('content')
<div class="container">
    <h1>メール認証が必要です</h1>
    <p>登録されたメールアドレスに認証リンクを送信しました。</p>
    <p>メールを確認し、リンクをクリックしてアカウントを有効にしてください。</p>

    @if (session('message'))
    <div class="alert alert-success">
        {{ session('message') }}
    </div>
    @endif

    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit" class="btn btn-primary">認証メールを再送信</button>
    </form>

    <br>
    <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
        ログアウト
    </a>
    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
    </form>
</div>
@endsection