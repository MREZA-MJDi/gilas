<!doctype html>
<html lang="fa" dir="rtl" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#141117">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <title>@yield('title', 'گیلاس')</title>
    <meta name="description" content="@yield('description', 'منوی دیجیتال و سفارش آنلاین گیلاس')">
    @unless(app()->environment('testing'))
        @vite(['resources/css/customer.css', 'resources/js/customer.js'])
    @endunless
</head>
<body class="gilas-public customer-page @yield('body_class') min-h-screen antialiased">
    @include('components.footsteps')

    <div class="gilas-content">
        @yield('content')
    </div>

    @unless(View::hasSection('hide_footer'))
        <footer class="gilas-site-footer" aria-label="پاورقی گیلاس">
            <div class="ui-shell gilas-site-footer__inner">
                <a href="{{ route('home') }}" class="gilas-site-footer__brand" aria-label="بازگشت به گیلاس">
                    <img src="{{ asset('favicon.svg') }}" alt="" width="36" height="36" decoding="async">
                    <span>گیلاس</span>
                </a>
                <nav class="gilas-site-footer__nav" aria-label="مسیرهای گیلاس">
                    <a href="{{ route('menu.index') }}">منو</a>
                    <a href="{{ route('public.reservation') }}">رزرو</a>
                    <a href="{{ route('public.location') }}">مسیریابی</a>
                </nav>
            </div>
        </footer>
    @endunless
</body>
</html>
