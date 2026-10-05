@php
    $logoUrl = $restaurant?->logo_path
        ? \Illuminate\Support\Facades\Storage::disk('public')->url($restaurant->logo_path)
        : null;
    $coverUrl = $restaurant?->cover_image_path
        ? \Illuminate\Support\Facades\Storage::disk('public')->url($restaurant->cover_image_path)
        : null;
@endphp
<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#3d5d5c">
    <meta name="description" content="{{ $restaurant?->description ?: 'خانه گیلاسی؛ طعم، فضا و لحظه‌ای برای مکث.' }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <title>{{ $restaurant?->name ?: 'خانه گیلاسی' }}</title>
    @unless(app()->environment('testing'))
        @vite(['resources/css/landing.css', 'resources/js/landing.js'])
    @endunless
</head>
<body class="home" data-home>
    <header class="home-header">
        <div class="ui-shell home-header__inner">
            <a class="home-brand" href="{{ route('home') }}">
                <span class="home-brand__mark">
                    @if($logoUrl)<img src="{{ $logoUrl }}" alt="" width="44" height="44">@else<span>{{ mb_substr($restaurant?->name ?? 'گیلاس', 0, 1) }}</span>@endif
                </span>
                <span><strong>{{ $restaurant?->name ?? 'خانه گیلاسی' }}</strong><span>کافه‌ای برای مکث‌های خوب</span></span>
            </a>
            <nav class="home-nav" aria-label="ناوبری">
                <a href="{{ route('public.story') }}">داستان</a>
                <a href="{{ route('public.location') }}">موقعیت</a>
                @if($restaurant?->settings?->reservation_enabled)
                    <a href="{{ route('public.reservation') }}">رزرو</a>
                @endif
                <a class="home-nav__menu" href="{{ route('menu.index') }}">دیدن منو</a>
            </nav>
        </div>
    </header>

    <main>
        <section class="home-hero" data-home-hero>
            @if($coverUrl)
                <div class="home-hero__media">
                    <img data-home-hero-image src="{{ $coverUrl }}" alt="{{ $restaurant?->name ?? 'خانه گیلاسی' }}" width="1800" height="1100" fetchpriority="high">
                </div>
            @endif
            <div class="home-hero__veil"></div>
            <div class="ui-shell home-hero__content">
                <div class="home-hero__copy" data-home-reveal>
                    <span class="home-hero__eyebrow">خانه گیلاسی</span>
                    <h1>طعم خوب،<br>با عجله نمی‌آید.</h1>
                    <p>{{ $restaurant?->description ?: 'قهوه، غذا و دسر؛ در فضایی که برای چند دقیقه بیشتر ماندن ساخته شده.' }}</p>
                    <div class="home-hero__actions">
                        <a class="ui-button ui-button--warm" href="{{ route('menu.index') }}">منو و قیمت‌ها</a>
                        @if($restaurant?->settings?->reservation_enabled)
                            <a class="ui-button ui-button--ghost" href="{{ route('public.reservation') }}">رزرو میز</a>
                        @endif
                    </div>
                    <div class="home-hero__meta">
                        <span>قیمت‌های واقعی منو</span>
                        <span>سفارش مستقیم از میز</span>
                        @if($restaurant?->address)<span>{{ $restaurant->address }}</span>@endif
                    </div>
                </div>
            </div>
            <span class="home-hero__scroll">SCROLL ↓</span>
        </section>

        <section class="home-section home-featured">
            <div class="ui-shell">
                <div class="home-section__head" data-home-reveal>
                    <div>
                        <span class="ui-kicker">انتخاب‌های خانه</span>
                        <h2>چیزهایی که باید<br>یک‌بار امتحانشان کنی.</h2>
                    </div>
                    <div class="home-slider__controls">
                        <button type="button" class="home-slider__button" data-home-slider-prev aria-label="قبلی">→</button>
                        <button type="button" class="home-slider__button" data-home-slider-next aria-label="بعدی">←</button>
                    </div>
                </div>

                <div class="home-slider">
                    <div class="home-slider__track" data-home-slider-track>
                        @forelse($featuredItems as $item)
                            <div class="home-slider__item">
                                <x-menu-item-card :item="$item" :restaurant="$restaurant" :category-name="$item->category?->name" />
                            </div>
                        @empty
                            <div class="public-menu-empty"><h2>فعلاً آیتمی ثبت نشده.</h2><p>محصولات از داشبورد مدیریت منو وارد می‌شوند.</p></div>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>

        <section class="home-section home-section--soft">
            <div class="ui-shell">
                <div class="home-section__head" data-home-reveal>
                    <div>
                        <span class="ui-kicker">سریع‌ترین راه</span>
                        <h2>بگو برای چه کاری<br>آمدی.</h2>
                    </div>
                </div>
                <div class="home-action-grid">
                    <a class="home-action" href="{{ route('menu.index') }}" data-home-reveal>
                        <div><em>01</em><strong class="block mt-3">منو و سفارش</strong><span class="block mt-2">غذاها، نوشیدنی‌ها، انتخاب‌های قابل شخصی‌سازی و قیمت واقعی.</span></div>
                        <span>کشف منو ←</span>
                    </a>
                    <a class="home-action" href="{{ route('public.reservation') }}" data-home-reveal>
                        <div><em>02</em><strong class="block mt-3">رزرو میز</strong><span class="block mt-2">برای قرار بعدی، قبل از رسیدن میزت را هماهنگ کن.</span></div>
                        <span>هماهنگی میز ←</span>
                    </a>
                    <a class="home-action" href="{{ route('public.location') }}" data-home-reveal>
                        <div><em>03</em><strong class="block mt-3">پیدا کردن ما</strong><span class="block mt-2">{{ $restaurant?->address ?: 'آدرس از تنظیمات رستوران خوانده می‌شود.' }}</span></div>
                        <span>مسیریابی ←</span>
                    </a>
                </div>
            </div>
        </section>

        <section class="home-section">
            <div class="ui-shell home-story">
                <div class="home-story__copy" data-home-reveal>
                    <span class="ui-kicker">داستان خانه</span>
                    <h2>یک کافه فقط<br>جایی برای قهوه نیست.</h2>
                    <p>{{ $restaurant?->description ?: 'خانه گیلاسی از همان جایی شروع می‌شود که یک نوشیدنی خوب، یک میز مناسب و چند دقیقه وقت آزاد کنار هم قرار می‌گیرند.' }}</p>
                    <a class="ui-button ui-button--primary mt-6" href="{{ route('public.story') }}">قصه‌ی گیلاس</a>
                </div>
                @if($coverUrl)
                    <div class="home-story__visual" data-home-reveal>
                        <img src="{{ $coverUrl }}" alt="" width="900" height="1100" loading="lazy">
                    </div>
                @endif
            </div>
        </section>
    </main>

    <footer class="home-footer">
        <div class="ui-shell home-footer__inner">
            <a class="home-footer__brand" href="{{ route('home') }}">{{ $restaurant?->name ?? 'خانه گیلاسی' }}</a>
            <div class="flex flex-wrap gap-4">
                <a href="{{ route('menu.index') }}">منو</a>
                <a href="{{ route('public.reservation') }}">رزرو</a>
                <a href="{{ route('public.location') }}">موقعیت</a>
                <a href="{{ route('public.story') }}">داستان</a>
            </div>
        </div>
    </footer>
</body>
</html>
