<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title')</title>
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    @yield('css')
</head>

<body>
    <header class="header">
        @if(Auth::user()->role === 1)
        <!-- 管理者用ヘッダー -->
        <a class="header__logo" href="{{ route('admin.attendance.index') }}">
            <img src="{{ asset('storage/image/logo.svg') }}" alt="COACHTECH">
        </a>
        <nav class="header__nav">
            <form action="{{ route('admin.attendance.index', ['date' => now()->format('Y-m-d')]) }}" method="GET">
                <button type="submit" class="nav-btn">勤怠一覧</button>
            </form>
            <form action="{{ route('admin.staff.index') }}" method="GET">
                <button type="submit" class="nav-btn">スタッフ一覧</button>
            </form>
            <form action="{{ route('stamp_correction_request.list') }}" method="GET">
                <button type="submit" class="nav-btn">申請一覧</button>
            </form>
            <form action="{{ route('admin.logout') }}" method="POST" class="logout-form">
                @csrf
                <button type="submit" class="logout-btn">ログアウト</button>
            </form>
        </nav>
        @else
        <!-- 一般ユーザー用ヘッダー -->
        <a class="header__logo" href="/">
            <img src="{{ asset('storage/image/logo.svg') }}" alt="COACHTECH">
        </a>
        <nav class="header__nav">
            <form action="{{ route('attendance.index') }}" method="GET">
                <button type="submit" class="nav-btn">勤怠</button>
            </form>
            <form action="{{ route('attendance.list') }}" method="GET">
                <button type="submit" class="nav-btn">勤怠一覧</button>
            </form>
            <form action="{{ route('stamp_correction_request.list') }}" method="GET">
                <button type="submit" class="nav-btn">申請</button>
            </form>
            <form action="{{ route('logout') }}" method="POST" class="logout-form">
                @csrf
                <button type="submit" class="logout-btn">ログアウト</button>
            </form>
        </nav>
        @endif
    </header>

    @yield('content')

</body>

</html>