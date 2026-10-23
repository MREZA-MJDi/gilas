<!doctype html>
<html lang="fa" dir="rtl" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#243b36">
    <meta name="description" content="@yield('description', $restaurant?->description ?: 'منوی دیجیتال، سفارش میز و تجربه خانه گیلاسی.')">
    <meta property="og:site_name" content="{{ $restaurant?->name ?: 'خانه گیلاسی' }}">
    <meta property="og:locale" content="fa_IR">
    <meta property="og:type" content="@yield('og-type', 'website')">
    <meta property="og:title" content="@yield('title', ($restaurant?->name ?: 'خانه گیلاسی') . ' — کافه و رستوران')">
    <meta property="og:description" content="@yield('description', $restaurant?->description ?: 'منوی دیجیتال، سفارش میز و تجربه خانه گیلاسی.')">
    @if($restaurant?->cover_image_path)
        <meta property="og:image" content="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($restaurant->cover_image_path) }}">
        <meta name="twitter:card" content="summary_large_image">
    @else
        <meta name="twitter:card" content="summary">
    @endif
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <title>@yield('title', ($restaurant?->name ?: 'خانه گیلاسی') . ' — کافه و رستوران')</title>
    @unless(app()->environment('testing'))
        @vite(['resources/css/customer.css', 'resources/js/customer.js'])
    @endunless
    @stack('styles')
</head>
<body class="customer-page">
<div class="customer-shell">
    <header class="gilas-site-header">
        <div class="gilas-site-header__inner">
            <a class="gilas-site-brand" href="{{ route('home') }}" aria-label="صفحه اصلی {{ $restaurant?->name ?: 'خانه گیلاسی' }}">
                <span class="gilas-site-brand__mark">
                    @if($restaurant?->logo_path)
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($restaurant->logo_path) }}" alt="" width="44" height="44">
                    @else
                        <span>{{ mb_substr($restaurant?->name ?: 'گ', 0, 1) }}</span>
                    @endif
                </span>
                <span class="gilas-site-brand__copy">
                    <strong>{{ $restaurant?->name ?: 'خانه گیلاسی' }}</strong>
                    <small>کافه، رستوران و تجربهٔ میز</small>
                </span>
            </a>

            <nav class="gilas-site-nav" aria-label="ناوبری اصلی">
                <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'is-current' : '' }}">خانه</a>
                <a href="{{ route('menu.index') }}" class="{{ request()->routeIs('menu.*') ? 'is-current' : '' }}">منو</a>
                <a href="{{ route('public.experience') }}" class="{{ request()->routeIs('public.experience') ? 'is-current' : '' }}">تجربهٔ ما</a>
                <a href="{{ route('public.reservation') }}" class="{{ request()->routeIs('public.reservation') ? 'is-current' : '' }}">رزرو میز</a>
                <a href="{{ route('public.location') }}" class="{{ request()->routeIs('public.location') ? 'is-current' : '' }}">موقعیت</a>
            </nav>

            <div class="gilas-site-header__actions">
                @if($restaurant?->phone)
                    <a class="gilas-site-phone" href="tel:{{ preg_replace('/[^0-9+]/', '', $restaurant->phone) }}" aria-label="تماس با رستوران">
                        <span aria-hidden="true">↗</span><span>تماس</span>
                    </a>
                @endif
                <a class="gilas-site-order" href="{{ route('menu.index') }}">دیدن منو <span aria-hidden="true">←</span></a>
            </div>
        </div>
    </header>

    @yield('content')

    <footer class="gilas-site-footer">
        <div class="gilas-site-footer__top">
            <div class="gilas-site-footer__brand">
                <a class="gilas-site-brand" href="{{ route('home') }}">
                    <span class="gilas-site-brand__mark">
                        @if($restaurant?->logo_path)
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($restaurant->logo_path) }}" alt="" width="44" height="44" loading="lazy">
                        @else
                            <span>{{ mb_substr($restaurant?->name ?: 'گ', 0, 1) }}</span>
                        @endif
                    </span>
                    <span class="gilas-site-brand__copy"><strong>{{ $restaurant?->name ?: 'خانه گیلاسی' }}</strong><small>یک مکث خوش‌طعم</small></span>
                </a>
                <p>{{ $restaurant?->description ?: 'منو را کشف کن، میزت را هماهنگ کن و تجربه‌ات را از همین‌جا شروع کن.' }}</p>
            </div>
            <nav class="gilas-site-footer__links" aria-label="دسترسی سریع">
                <strong>دسترسی سریع</strong>
                <a href="{{ route('menu.index') }}">منوی غذا و نوشیدنی</a>
                <a href="{{ route('public.reservation') }}">رزرو و هماهنگی میز</a>
                <a href="{{ route('public.story') }}">داستان خانه</a>
                <a href="{{ route('public.location') }}">آدرس و مسیریابی</a>
            </nav>
            <div class="gilas-site-footer__contact">
                <strong>برای هماهنگی</strong>
                @if($restaurant?->phone)
                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', $restaurant->phone) }}">{{ $restaurant->phone }}</a>
                @endif
                @if($restaurant?->address)
                    <p>{{ $restaurant->address }}</p>
                @else
                    <p>اطلاعات تماس و نشانی از تنظیمات رستوران نمایش داده می‌شود.</p>
                @endif
                <a class="gilas-site-footer__map" href="{{ route('public.location') }}">مسیریابی <span aria-hidden="true">↗</span></a>
            </div>
        </div>
        <div class="gilas-site-footer__bottom">
            <span>© {{ now()->year }} {{ $restaurant?->name ?: 'خانه گیلاسی' }}</span>
            <a href="{{ route('home') }}">بازگشت به بالای صفحه ↑</a>
        </div>
    </footer>
</div>
</body>
</html>
