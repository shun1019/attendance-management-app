<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    @yield('css')
</head>

<body>

    <header class="auth-header">
        <a class="auth-header__logo">
            <img src="{{ asset('storage/image/logo.svg') }}" alt="COACHTECH">
        </a>
    </header>

    <div class="form-container">
        @yield('content')
    </div>

</body>

</html>