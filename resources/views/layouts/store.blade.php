<!doctype html>
<html lang="fa" dir="rtl" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0b0908">
    <meta name="description" content="@yield('meta-description', $restaurant?->description ?: 'منوی گیلاس؛ غذاها، نوشیدنی‌ها، رزرو میز و سفارش مستقیم از میز.')">
    <link rel="canonical" href="@yield('canonical', url()->current())">
    <meta property="og:site_name" content="{{ $restaurant?->name ?: 'گیلاس' }}">
    <meta property="og:locale" content="fa_IR">
    <meta property="og:type" content="@yield('og-type', 'website')">
    <meta property="og:title" content="@yield('title', ($restaurant?->name ?: 'گیلاس') . ' — کافه و رستوران')">
    <meta property="og:description" content="@yield('meta-description', $restaurant?->description ?: 'منوی گیلاس؛ غذاها، نوشیدنی‌ها، رزرو میز و سفارش مستقیم از میز.')">
    @if (filled(trim($__env->yieldContent('og-image'))))
        <meta property="og:image" content="{{ trim($__env->yieldContent('og-image')) }}">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:image" content="{{ trim($__env->yieldContent('og-image')) }}">
    @elseif($restaurant?->cover_image_path)
        <meta property="og:image" content="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($restaurant->cover_image_path) }}">
        <meta name="twitter:card" content="summary_large_image">
    @else
        <meta name="twitter:card" content="summary">
    @endif
    <meta name="twitter:title" content="@yield('title', ($restaurant?->name ?: 'گیلاس') . ' — کافه و رستوران')">
    <meta name="twitter:description" content="@yield('meta-description', $restaurant?->description ?: 'منوی گیلاس و سفارش از میز.')">
    <title>@yield('title', ($restaurant?->name ?: 'گیلاس') . ' — کافه و رستوران')</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    @unless(app()->environment('testing'))
        @vite(['resources/css/customer.css', 'resources/css/storefront.css', 'resources/css/home-hero.css', 'resources/js/customer.js', 'resources/js/storefront.js'])
    @endunless
    @stack('styles')
</head>
<body class="customer-page storefront-body {{ request()->routeIs('home', 'storefront.index') ? 'storefront-home-body' : '' }}">
<div class="customer-shell store-shell {{ request()->routeIs('home', 'storefront.index') ? 'store-shell--home' : '' }}" data-storefront>
    @unless(request()->routeIs('home', 'storefront.index'))
    <header class="gilas-site-header store-gilas-header">
        <div class="gilas-site-header__inner">
            <a class="gilas-site-brand" href="{{ route('home') }}" aria-label="صفحه اصلی {{ $restaurant?->name ?: 'گیلاس' }}">
                <span class="gilas-site-brand__mark">
                    @if($restaurant?->logo_path)
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($restaurant->logo_path) }}" alt="" width="44" height="44">
                    @else
                        <span>{{ mb_substr($restaurant?->name ?: 'گ', 0, 1) }}</span>
                    @endif
                </span>
                <span class="gilas-site-brand__copy"><strong>{{ $restaurant?->name ?: 'گیلاس' }}</strong><small>کافه، رستوران و تجربهٔ میز</small></span>
            </a>
            <nav class="gilas-site-nav" aria-label="ناوبری اصلی">
                <a href="{{ route('home') }}" class="{{ request()->routeIs('home', 'storefront.index') ? 'is-current' : '' }}">خانه</a>
                <a href="{{ route('menu.index') }}" class="{{ request()->routeIs('menu.*', 'shop.index', 'products.*') ? 'is-current' : '' }}">منو</a>
                <a href="{{ route('public.experience') }}" class="{{ request()->routeIs('public.experience', 'public.story') ? 'is-current' : '' }}">تجربهٔ ما</a>
                <a href="{{ route('public.reservation') }}" class="{{ request()->routeIs('public.reservation') ? 'is-current' : '' }}">رزرو میز</a>
                <a href="{{ route('public.location') }}" class="{{ request()->routeIs('public.location') ? 'is-current' : '' }}">موقعیت</a>
            </nav>
            <div class="gilas-site-header__actions">
                <form class="store-search gilas-store-search" action="{{ route('menu.index') }}" method="GET" role="search">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.7"></circle><path d="m16 16 4.2 4.2"></path></svg>
                    <input type="search" name="q" value="{{ request('q', $search ?? '') }}" placeholder="جست‌وجوی غذا" aria-label="جست‌وجو در منو">
                    <button type="submit" aria-label="اجرای جست‌وجو">↵</button>
                </form>
                <a class="gilas-site-order" href="{{ route('cart.index') }}" aria-label="سبد سفارش از میز">سبد سفارش <span aria-hidden="true">←</span></a>
            </div>
        </div>
    </header>
    @endunless

    @if(session('status'))
        <div class="store-session-status" role="status">{{ session('status') }}</div>
    @endif
    @yield('content')

    <footer class="gilas-site-footer store-gilas-footer">
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
                    <span class="gilas-site-brand__copy"><strong>{{ $restaurant?->name ?: 'گیلاس' }}</strong><small>یک مکث خوش‌طعم</small></span>
                </a>
                <p>{{ $restaurant?->description ?: 'منو را کشف کن، میزت را هماهنگ کن و تجربه‌ات را از همین‌جا شروع کن.' }}</p>
            </div>
            <nav class="gilas-site-footer__links" aria-label="دسترسی سریع">
                <strong>دسترسی سریع</strong>
                <a href="{{ route('menu.index') }}">منوی غذا و نوشیدنی</a>
                <a href="{{ route('public.experience') }}">تجربهٔ خانه</a>
                <a href="{{ route('public.reservation') }}">رزرو و هماهنگی میز</a>
                <a href="{{ route('public.story') }}">داستان خانه</a>
                <a href="{{ route('public.location') }}">آدرس و مسیریابی</a>
            </nav>
            <div class="gilas-site-footer__contact">
                <strong>برای هماهنگی</strong>
                @if($restaurant?->phone)<a href="tel:{{ preg_replace('/[^0-9+]/', '', $restaurant->phone) }}">{{ $restaurant->phone }}</a>@endif
                <p>{{ $restaurant?->address ?: 'اطلاعات تماس و نشانی از تنظیمات رستوران نمایش داده می‌شود.' }}</p>
                <a class="gilas-site-footer__map" href="{{ route('public.location') }}">مسیریابی <span aria-hidden="true">↗</span></a>
            </div>
        </div>
        <div class="gilas-site-footer__bottom">
            <span>© {{ now()->year }} {{ $restaurant?->name ?: 'گیلاس' }}</span>
            <a href="{{ route('home') }}">بازگشت به بالای صفحه ↑</a>
        </div>
    </footer>
    <div class="store-toast" data-store-toast role="status" aria-live="polite"></div>
</div>
</body>
</html>
