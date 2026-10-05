<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#3d5d5c">
    <title>@yield('title', 'مدیریت خانه گیلاسی')</title>
    @unless(app()->environment('testing'))
        @vite(['resources/css/admin.css', 'resources/js/admin.js'])
    @endunless
</head>
<body class="admin-page">
<div class="admin-shell">
    <aside class="admin-sidebar">
        <a href="{{ route('admin.dashboard', $restaurant) }}" class="admin-brand">
            <span class="admin-brand__mark">گ</span>
            <span><strong>{{ $restaurant->name }}</strong><small>Operations</small></span>
        </a>

        <nav class="admin-nav" aria-label="ناوبری مدیریت">
            <a href="#top" class="is-active">داشبورد</a>
            <a href="#orders">سفارش‌ها</a>
            <a href="#kitchen">آشپزخانه</a>
            <a href="#tables">میزها و QR</a>
            <a href="#sales">گزارش فروش</a>
        </nav>

        <form method="POST" action="{{ route('admin.logout') }}" class="admin-sidebar__logout">
            @csrf
            <button type="submit">خروج از حساب</button>
        </form>
    </aside>

    <main class="admin-main" id="top">
        @yield('content')
    </main>

    <nav class="admin-mobile-nav" aria-label="ناوبری سریع">
        <a href="#top">داشبورد</a>
        <a href="#orders">سفارش‌ها</a>
        <a href="#kitchen">آشپزخانه</a>
        <a href="#tables">میزها</a>
    </nav>
</div>
</body>
</html>
