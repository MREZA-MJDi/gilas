<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#141117">
    <meta name="description" content="{{ $restaurant?->description ?: 'گیلاس — یک تجربه خوش‌طعم و متفاوت.' }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <title>{{ $restaurant?->name ?: 'گیلاس' }}</title>
    @unless(app()->environment('testing'))
        @vite(['resources/css/landing.css', 'resources/js/landing.js'])
    @endunless
</head>
<body class="gilas-landing">
    @include('components.footsteps')

    <main class="landing-stage" aria-label="ورود به گیلاس">
        <nav class="landing-nav" aria-label="مسیرهای سریع گیلاس">
            <div class="landing-nav__links">
                <a href="{{ route('menu.index') }}">منو</a>
                <a href="{{ route('menu.index') }}">سفارش</a>
            </div>

            <button id="switch"
                    class="vision-switch"
                    type="button"
                    aria-label="تغییر حالت نمایش"
                    aria-pressed="false">
                <span aria-hidden="true"></span>
            </button>

            <div class="landing-nav__links landing-nav__links--after">
                <a href="{{ route('public.reservation') }}">رزرو</a>
                <a href="{{ route('public.location') }}">مسیر</a>
            </div>
        </nav>

        <div class="landing-copy">
            <h1>از همین‌جا<br><strong>شروع کن.</strong></h1>
        </div>

        <div class="honey-detail" data-honey-detail>
            <span class="honey-detail__eyebrow">انتخاب تو</span>
            <h2 data-honey-title>{{ $honeycombItems[0]['title'] ?? 'منو' }}</h2>
            <p data-honey-text>{{ $honeycombItems[0]['text'] ?? 'از همین‌جا شروع کن.' }}</p>
            <a data-honey-action href="{{ $honeycombItems[0]['url'] ?? route('menu.index') }}">
                {{ $honeycombItems[0]['cta'] ?? 'دیدن منو' }}
            </a>
        </div>

        @php
            $honeycomb = [5, 6, 7, 8, 9, 8, 7, 6, 5];
            $flatIndex = 0;
            $navigationItems = count($honeycombItems);
        @endphp

        <div id="container"
             class="honeycomb"
             aria-label="مسیرهای گیلاس"
             data-honeycomb='@json($honeycombItems, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)'>
            @foreach ($honeycomb as $columnIndex => $column)
                <div class="honeycomb__column" style="--column: {{ $columnIndex }};">
                    @for ($cellIndex = 1; $cellIndex <= $column; $cellIndex++)
                        @php
                            $targetIndex = $flatIndex++;
                            $target = $navigationItems
                                ? $honeycombItems[$targetIndex % $navigationItems]
                                : ['title' => 'گیلاس', 'icon' => '🍒'];
                        @endphp
                        <button
                            type="button"
                            class="hexagon"
                            style="--index: {{ $cellIndex }}; --icon: '{{ $target['icon'] }}';"
                            aria-label="{{ $target['title'] }}"
                            data-honey-index="{{ $targetIndex }}">
                        </button>
                    @endfor
                </div>
            @endforeach
        </div>

    </main>
</body>
</html>
