<!doctype html>
<html lang="fa" dir="rtl" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#3d5d5c">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <title>@yield('title', 'خانه گیلاسی')</title>
    <meta name="description" content="@yield('description', 'منوی دیجیتال و تجربه خانه گیلاسی')">
    @unless(app()->environment('testing'))
        @vite(['resources/css/customer.css', 'resources/js/customer.js'])
    @endunless
</head>
<body class="customer-page">
    @include('components.footsteps')
    <div class="customer-shell">
        @yield('content')
    </div>
    <footer class="home-footer">
        <div class="ui-shell home-footer__inner">
            <a class="home-footer__brand" href="{{ route('home') }}">{{ $restaurant?->name ?? 'خانه گیلاسی' }}</a>
            <div class="flex flex-wrap gap-4">
                <a href="{{ route('menu.index') }}">منو</a>
                <a href="{{ route('public.location') }}">موقعیت</a>
                <a href="{{ route('public.story') }}">داستان</a>
            </div>
        </div>
    </footer>
</body>
</html>
