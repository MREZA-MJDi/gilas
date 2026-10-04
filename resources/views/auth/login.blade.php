<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#141117">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <title>ورود — گیلاس</title>
    @unless(app()->environment('testing'))
        @vite(['resources/css/admin.css', 'resources/js/admin.js'])
    @endunless
</head>
<body class="admin-page admin-login-page">
    <main class="admin-login">
        <section class="admin-login__panel" aria-labelledby="login-title">
            <a href="{{ route('home') }}" class="admin-login__brand" aria-label="بازگشت به گیلاس">
                <span>گ</span>
                <strong>گیلاس</strong>
            </a>

            <div class="admin-login__intro">
                <span>Operations Room</span>
                <h1 id="login-title">ورود به پنل گیلاس</h1>
                <p>سفارش، آشپزخانه، میزها، تحویل و صندوق را از یک‌جا کنترل کن.</p>
            </div>

            <form action="{{ route('login.store') }}" method="POST" class="admin-login__form">
                @csrf

                <label>
                    <span>ایمیل</span>
                    <input type="email"
                           name="email"
                           value="{{ old('email') }}"
                           autocomplete="email"
                           inputmode="email"
                           required
                           autofocus
                           placeholder="owner@gilas.test">
                </label>

                <label>
                    <span>رمز عبور</span>
                    <input type="password"
                           name="password"
                           autocomplete="current-password"
                           required
                           placeholder="••••••••">
                </label>

                <label class="admin-login__remember">
                    <input type="checkbox" name="remember" value="1">
                    <span>مرا به خاطر بسپار</span>
                </label>

                @if($errors->any())
                    <div class="admin-form-error">{{ $errors->first() }}</div>
                @endif

                <button type="submit">
                    ورود به Operations Room
                    <span aria-hidden="true">←</span>
                </button>
            </form>

            <a href="{{ route('home') }}" class="admin-login__back">بازگشت به گیلاس</a>
        </section>
    </main>
</body>
</html>
