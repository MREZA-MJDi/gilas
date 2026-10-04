<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#eee4d3">
    <meta name="description" content="{{ $restaurant?->description ?: 'خانه گیلاسی؛ قهوه، صبحانه، دسر و مکث‌های خوب.' }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Estedad:wght@400;500;600;700&display=swap" rel="stylesheet">
    <title>{{ $restaurant?->name ?: 'خانه گیلاسی' }}</title>
    @unless(app()->environment('testing'))
        @vite(['resources/css/landing.css', 'resources/js/landing.js'])
    @endunless
</head>
<body class="gilas-landing">
    <main class="landing-stage" aria-label="خانه گیلاسی">
        <div class="landing-scene" aria-hidden="true">
            <div class="landing-scene__wash"></div>
            <div class="tree-art">
                <svg class="tree-art__svg" viewBox="0 0 2556 3440" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path class="tree-branch tree-branch--bottom" d="M1278.07 3439.61L1278.07 1717"/>
                    <path class="tree-branch" data-tree-branch="top" d="M1278.07 1722.61L1278.07 0"/>
                    <path class="tree-branch" data-tree-branch="left" d="M1278.07 1716.2L2 651.193"/>
                    <path class="tree-branch" data-tree-branch="left-top" d="M1278.07 1716.2L552.609 176.432"/>
                    <path class="tree-branch" data-tree-branch="right-top" d="M1278.08 1716.2L2002.43 176.432"/>
                    <path class="tree-branch" data-tree-branch="right" d="M1278.07 1716.2L2554.09 651.193"/>
                </svg>
                <span class="tree-art__orbit tree-art__orbit--outer"></span>
                <span class="tree-art__orbit tree-art__orbit--inner"></span>
                <span class="cicada cicada--left"></span>
                <span class="cicada cicada--top"></span>
                <span class="cicada cicada--right"></span>
            </div>
        </div>

        <nav class="landing-nav" aria-label="ناوبری اصلی">
            <a class="landing-brand" href="{{ route('home') }}" aria-label="خانه گیلاسی">
                <span class="landing-brand__mark">گ</span>
                <span><strong>خانه گیلاسی</strong><small>café · paris mood</small></span>
            </a>
            <div class="landing-nav__links">
                @foreach ($primaryLinks as $link)
                    <a href="{{ $link['url'] }}">{{ $link['label'] }}</a>
                @endforeach
            </div>
        </nav>

        <section class="landing-intro" aria-labelledby="landing-title">
            <span class="landing-intro__eyebrow">یک میز، یک فنجان، یک مکث</span>
            <h1 id="landing-title">قهوه‌ات را<br><strong>آرام انتخاب کن.</strong></h1>
            <p>{{ $restaurant?->description ?: 'کافه‌ای گرم برای قهوه، صبحانه، دسر و گفت‌وگوهای طولانی.' }}</p>
            <a class="landing-intro__cta" href="{{ route('menu.index') }}"><span>دیدن منو و سفارش</span><span aria-hidden="true">←</span></a>
        </section>

        <section class="category-panel" aria-labelledby="category-title">
            <div class="category-panel__heading">
                <div><span>از منوی خانه</span><h2 id="category-title">برای هر حال، یک انتخاب</h2></div>
                <a href="{{ route('menu.index') }}">همه منو <span aria-hidden="true">←</span></a>
            </div>
            <div id="container" class="category-grid" aria-label="دسته‌بندی‌های منو" data-honeycomb='@json($honeycombItems, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)'>
                @forelse ($honeycombItems as $index => $item)
                    <a class="category-card" href="{{ $item['url'] }}" data-category-index="{{ $index }}">
                        <span class="category-card__icon" aria-hidden="true">
                            @switch($item['icon'])
                                @case('coffee') <svg viewBox="0 0 24 24"><path d="M5 8h12v6a4 4 0 0 1-4 4H9a4 4 0 0 1-4-4V8Zm12 2h1a2.5 2.5 0 0 1 0 5h-1M8 5c0-1 1-1.5 1-2M12 5c0-1 1-1.5 1-2"/></svg> @break
                                @case('sun') <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg> @break
                                @case('cake') <svg viewBox="0 0 24 24"><path d="M4 11h16v9H4zM4 15h16M7 11V8h3v3M14 11V8h3v3M8.5 5.5c0-1 1-1.5 1-2M15.5 5.5c0-1 1-1.5 1-2"/></svg> @break
                                @case('glass') <svg viewBox="0 0 24 24"><path d="M6 3h12l-1.5 7.5A5 5 0 0 1 12 14a5 5 0 0 1-4.5-3.5L6 3ZM12 14v5M8 21h8"/></svg> @break
                                @case('spark') <svg viewBox="0 0 24 24"><path d="m12 2 1.5 6.5L20 10l-6.5 1.5L12 18l-1.5-6.5L4 10l6.5-1.5L12 2ZM19 17l.6 2.4L22 20l-2.4.6L19 23l-.6-2.4L16 20l2.4-.6L19 17Z"/></svg> @break
                                @case('burger') <svg viewBox="0 0 24 24"><path d="M4 11c0-4 3.6-7 8-7s8 3 8 7M4 11h16M5 14h14M6 17h12l-1 3H7l-1-3ZM3 11h18v3H3z"/></svg> @break
                                @case('fries') <svg viewBox="0 0 24 24"><path d="m7 4 1 7M11 3v8M15 4l-1 7M19 5l-2 6M5 11h14l-2 10H7L5 11Z"/></svg> @break
                                @case('pasta') <svg viewBox="0 0 24 24"><path d="M4 13h16M5 13c0 4 3 7 7 7s7-3 7-7M7 10c2-2 4-2 5 0 1-2 3-2 5 0M9 5c2 1 3 2 3 5M15 5c-2 1-3 2-3 5"/></svg> @break
                                @default <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="8"/><path d="M8 12h8M12 8v8"/></svg>
                            @endswitch
                        </span>
                        <span class="category-card__content"><strong>{{ $item['title'] }}</strong><small>{{ $item['text'] }}</small></span>
                        <span class="category-card__arrow" aria-hidden="true">↗</span>
                    </a>
                @empty
                    <a class="category-card category-card--empty" href="{{ route('menu.index') }}"><span class="category-card__icon">☕</span><span class="category-card__content"><strong>منوی گیلاس</strong><small>انتخابت را از منوی کامل شروع کن.</small></span><span class="category-card__arrow">↗</span></a>
                @endforelse
            </div>
        </section>

        <div class="landing-stamp" aria-hidden="true"><span>خانه گیلاسی</span><span>slow coffee · good company</span></div>
    </main>
</body>
</html>
