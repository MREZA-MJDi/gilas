<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#3d5d5c">
    <title>ورود مدیریت — خانه گیلاسی</title>
    @unless(app()->environment('testing'))
        @vite(['resources/css/admin.css', 'resources/js/admin.js'])
    @endunless
</head>
<body class="admin-page admin-auth-page">
<main class="admin-auth">
    <section class="admin-auth__card">
        <div class="admin-auth__brand">
            <span>گ</span>
            <div><strong>خانه گیلاسی</strong><small>مدیریت کافه و رستوران</small></div>
        </div>

        <div class="admin-auth__intro">
            <p class="admin-kicker">ورود امن</p>
            <h1>خوش برگشتی.</h1>
            <p>برای ورود به اتاق عملیات، سفارش‌ها، منو، میزها و گزارش‌ها آماده‌ای؟</p>
        </div>

        @if($errors->any())
            <div class="admin-alert">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('admin.login.store') }}" class="admin-form">
            @csrf
            <label>
                ایمیل
                <input name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
            </label>

            <label>
                رمز عبور
                <input name="password" type="password" autocomplete="current-password" required>
            </label>

            <label class="admin-check">
                <input name="remember" type="checkbox" value="1">
                <span>مرا به خاطر بسپار</span>
            </label>

            <button type="submit">ورود به داشبورد</button>
        </form>
    </section>
</main>
</body>
</html>
