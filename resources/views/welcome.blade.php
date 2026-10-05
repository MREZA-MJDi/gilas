<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#F7F3ED">
    <meta name="description" content="{{ $restaurant?->description ?: 'خانه گیلاسی — کافه‌ای برای طعم، مکث و حال خوب.' }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <title>{{ $restaurant?->name ?: 'خانه گیلاسی' }}</title>
    @unless(app()->environment('testing'))
        @vite(['resources/css/landing.css', 'resources/js/landing.js'])
    @endunless
</head>
<body class="gilas-landing">
<div class="landing-shell">
    <header class="landing-nav">
        <a href="{{ route('home') }}" class="landing-brand" aria-label="خانه گیلاسی">
            <span class="landing-brand__mark"><img src="{{ asset('favicon.svg') }}" alt="" width="38" height="38"></span>
            <span>{{ $restaurant?->name ?: 'خانه گیلاسی' }}</span>
        </a>
        <nav class="landing-nav__links" aria-label="ناوبری اصلی">
            <a href="{{ route('menu.index') }}">منو</a>
            <a href="{{ route('public.reservation') }}">رزرو</a>
            <a href="{{ route('public.location') }}">مسیریابی</a>
            <a href="{{ route('public.story') }}">داستان</a>
            <a class="landing-nav__cta" href="{{ route('menu.index') }}">شروع سفارش</a>
        </nav>
    </header>

    <main>
        <section class="landing-hero">
            <div class="landing-copy">
                <span class="landing-copy__eyebrow">خانه گیلاسی</span>
                <h1>یک مکث خوب،<strong>با طعم واقعی.</strong></h1>
                <p class="landing-copy__text">ظاهر خاص است، اما مسیرها واضح‌اند: ببین، انتخاب کن و با چند لمس ادامه بده.</p>
                <div class="landing-copy__actions">
                    <a href="{{ route('menu.index') }}" class="landing-action landing-action--primary">دیدن منوی واقعی ↗</a>
                    <a href="{{ route('public.reservation') }}" class="landing-action landing-action--secondary">رزرو میز</a>
                </div>
                <div class="landing-signals">
                    <span class="landing-signal">منوی متصل به داده</span>
                    <span class="landing-signal">سفارش مستقیم</span>
                    <span class="landing-signal">رزرو و مسیریابی</span>
                </div>
            </div>

            <div class="landing-hero__product">
                @include('components.product-motion-card', ['products' => $featuredItems, 'label' => 'انتخاب امروز'])
            </div>
        </section>

        <section class="landing-section" aria-labelledby="landing-next-heading">
            <div class="landing-section__heading">
                <div><span class="landing-copy__eyebrow">مسیر بعدی</span><h2 id="landing-next-heading">از همین‌جا ادامه بده.</h2></div>
                <p>بدون maze و بدون صفحه‌ی بن‌بست؛ هر مسیر اصلی با یک action روشن شروع می‌شود.</p>
            </div>
            <div class="landing-copy__actions">
                <a href="{{ route('public.experience') }}" class="landing-action landing-action--secondary">تجربه‌ی گیلاس</a>
                <a href="{{ route('public.story') }}" class="landing-action landing-action--secondary">داستان خانه</a>
                <a href="{{ route('public.location') }}" class="landing-action landing-action--secondary">آدرس و مسیر</a>
            </div>
        </section>
    </main>
</div>
</body>
</html>
