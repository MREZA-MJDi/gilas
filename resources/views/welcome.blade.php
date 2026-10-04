<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#f5eee4">
    <meta name="description" content="{{ $restaurant?->description ?: 'گیلاس — یک تجربه خوش‌طعم و متفاوت.' }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <title>{{ $restaurant?->name ?: 'گیلاس' }}</title>
    @unless(app()->environment('testing'))
        @vite(['resources/css/landing.css', 'resources/js/landing.js'])
    @endunless
</head>
<body class="gilas-landing">
    @include('components.footsteps')

    <main class="landing-stage" aria-label="{{ $restaurant?->name ?: 'گیلاس' }}">
        <nav class="landing-nav" aria-label="ناوبری اصلی گیلاس">
            <a class="landing-nav__brand" href="{{ route('home') }}" aria-label="خانه گیلاس"><span>گیلاس</span></a>
            <div class="landing-nav__links">
                <a href="{{ route('home') }}">خانه</a>
                <a href="{{ route('menu.index') }}">منو</a>
                <a href="{{ route('public.experience') }}">تجربه</a>
                <a href="{{ route('public.reservation') }}">رزرو</a>
                <a href="{{ route('public.story') }}">داستان</a>
                <a href="{{ route('public.club') }}">باشگاه</a>
                <a href="{{ route('public.location') }}">مسیریابی</a>
            </div>
            <button id="switch" class="vision-switch" type="button" aria-label="تغییر حالت نمایش" aria-pressed="false"><span aria-hidden="true"></span></button>
        </nav>

        <div class="landing-copy">
            <span class="landing-kicker">خانه گیلاس</span>
            <h1>یک تجربه<br><strong>خوش‌طعم و متفاوت</strong></h1>
            <p>{{ $restaurant?->description ?: 'منو، فضا و حال‌وهوای گیلاس؛ همه‌چیز از یک لمس شروع می‌شود.' }}</p>
        </div>

        <div class="honey-detail" data-honey-detail aria-live="polite">
            <span class="honey-detail__eyebrow">انتخاب تو</span>
            <h2 data-honey-title>شروع کن</h2>
            <p data-honey-text>یک سلول را انتخاب کن؛ دسته‌های غذا مسیر خودشان را دارند و بعضی سلول‌ها خود Landing را به تو یاد می‌دهند.</p>
            <a data-honey-action href="{{ route('menu.index') }}">دیدن منو</a>
        </div>

        @php
            $honeycomb = [5, 6, 7, 8, 9, 8, 7, 6, 5];
            $flatIndex = 0;
            $categoryPositions = [3, 10, 17, 24, 31, 38, 45, 52];
            $guidePositions = [0, 8, 16, 28, 36, 44, 60];
            $categoryItems = collect($honeycombItems)->where('type', 'category')->values();
            $guideItems = collect($honeycombItems)->where('type', 'guide')->values();
            $categoryCursor = 0;
            $guideCursor = 0;
        @endphp

        <div id="container"
             class="honeycomb"
             aria-label="دسته‌های غذا و راهنمای تعاملی گیلاس"
             data-honeycomb='@json($honeycombItems, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)'>
            @foreach ($honeycomb as $columnIndex => $column)
                <div class="honeycomb__column" style="--column: {{ $columnIndex }};">
                    @for ($cellIndex = 1; $cellIndex <= $column; $cellIndex++)
                        @php
                            $targetIndex = $flatIndex++;
                            $type = 'decorative';
                            $target = ['title' => 'گیلاس', 'text' => 'این سلول برای ریتم و حرکت Landing است.', 'cta' => 'راهنما', 'icon' => '', 'url' => null];

                            if (in_array($targetIndex, $categoryPositions, true) && $categoryItems->has($categoryCursor)) {
                                $type = 'category';
                                $target = $categoryItems[$categoryCursor++];
                            } elseif (in_array($targetIndex, $guidePositions, true) && $guideItems->has($guideCursor)) {
                                $type = 'guide';
                                $target = $guideItems[$guideCursor++];
                            }
                        @endphp
                        <button type="button"
                                class="hexagon hexagon--{{ $type }}"
                                style="--index: {{ $cellIndex }}; --icon: '{{ $target['icon'] }}';"
                                aria-label="{{ $target['title'] }}"
                                data-honey-index="{{ $targetIndex }}"
                                data-honey-type="{{ $type }}"
                                data-honey-target="{{ $target['slug'] ?? '' }}"></button>
                    @endfor
                </div>
            @endforeach
        </div>

        <span class="landing-mark" aria-hidden="true">HOUSE OF GILAS</span>
    </main>
</body>
</html>
