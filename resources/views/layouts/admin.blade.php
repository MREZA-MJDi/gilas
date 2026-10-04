<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#141117">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <title>@yield('title', 'پنل مدیریت گیلاس')</title>
    @unless(app()->environment('testing'))
        @vite(['resources/css/admin.css', 'resources/js/admin.js'])
    @endunless
</head>
<body class="admin-page">
    <div class="admin-shell">
        <aside class="admin-sidebar">
            <a href="{{ route('admin.dashboard', $restaurant?->slug ?? '') }}" class="admin-brand" aria-label="داشبورد گیلاس">
                <span class="admin-brand__mark">گ</span>
                <span class="admin-brand__copy">
                    <strong>گیلاس</strong>
                    <small>Operations Room</small>
                </span>
            </a>

            <nav class="admin-nav" aria-label="ناوبری مدیریت">
                <a href="#overview" data-admin-link class="is-active">نمای کلی</a>
                <a href="#orders" data-admin-link>سفارش‌ها</a>
                <a href="#kitchen" data-admin-link>آشپزخانه</a>
                <a href="#tables" data-admin-link>میزها و QR</a>
                <a href="#delivery" data-admin-link>تحویل</a>
                <a href="#cashier" data-admin-link>صندوق</a>
            </nav>

            <div class="admin-sidebar__foot">
                <span>نسخه‌ی workspace</span>
                <strong>GILAS / LIVE</strong>
            </div>
        </aside>

        <main class="admin-main">
            @yield('content')
        </main>

        <nav class="admin-mobile-nav" aria-label="ناوبری سریع">
            <a href="#overview" data-admin-link class="is-active"><span>⌂</span><small>نمای کلی</small></a>
            <a href="#orders" data-admin-link><span>◉</span><small>سفارش</small></a>
            <a href="#kitchen" data-admin-link><span>◈</span><small>آشپزخانه</small></a>
            <a href="#tables" data-admin-link><span>▦</span><small>میزها</small></a>
            <a href="#delivery" data-admin-link><span>↗</span><small>تحویل</small></a>
        </nav>
    </div>
</body>
</html>
