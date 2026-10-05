<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#F7F3ED">
    <title>ورود مدیریت — خانه گیلاسی</title>
    @unless(app()->environment('testing'))
        @vite(['resources/css/admin.css', 'resources/js/admin.js'])
    @endunless
</head>
<body class="admin-page">
<main class="admin-login">
    <section class="admin-login__panel">
        <div class="admin-login__mark">گ</div>
        <h1>ورود به اتاق عملیات</h1>
        <p>برای مدیریت سفارش‌ها، منو، میزها و گزارش‌های خانه گیلاسی وارد شو.</p>
        @if($errors->any())<div class="admin-error">{{ $errors->first() }}</div>@endif
        <form method="POST" action="{{ route('admin.login.store') }}">
            @csrf
            <label class="admin-field"><span>ایمیل</span><input type="email" name="email" value="{{ old('email') }}" autocomplete="email" required autofocus></label>
            <label class="admin-field"><span>رمز عبور</span><input type="password" name="password" autocomplete="current-password" required></label>
            <label class="admin-field" style="display:flex;align-items:center;gap:8px">
                <input type="checkbox" name="remember" value="1" style="width:auto;height:auto">
                <span style="margin:0">مرا به خاطر بسپار</span>
            </label>
            <button type="submit" class="admin-submit">ورود به داشبورد</button>
        </form>
    </section>
</main>
</body>
</html>
