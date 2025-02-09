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
        <a class="header__logo" href="/">
            <img src="{{ asset('storage/image/logo.svg') }}" alt="COACHTECH">
        </a>
        <div class="search-bar">
            <form action="{{ route('index') }}" method="GET" class="search-form">
                <input type="text" name="search" placeholder="なにをお探しですか？" value="{{ request('search') }}" class="search-input">
                <button type="submit" class="search-icon-btn">
                    <img src="{{ asset('storage/image/検索アイコン1.jpg') }}" alt="検索" class="search-icon">
                </button>
            </form>
        </div>
        <div class="header__nav">
            <form action="{{ route('logout') }}" method="POST" class="logout-form">
                @csrf
                <button type="submit" class="logout-btn">ログアウト</button>
            </form>
            <form action="{{ route('profile.index') }}" method="GET">
                <button type="submit" class="mypage-btn">マイページ</button>
            </form>
            <form action="{{ route('sell') }}" method="GET">
                <button type="submit" class="sell-btn">出品</button>
            </form>
        </div>
    </header>

    @yield('content')
    @yield('js')

</body>

</html>