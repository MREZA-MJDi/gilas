<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#0b090d">
    <meta name="description" content="{{ $restaurant?->description ?: 'گیلاس — یک تجربه خوش‌طعم و متفاوت.' }}">
    <title>{{ $restaurant?->name ?: 'گیلاس' }}</title>
    @unless(app()->environment('testing'))
        @vite(['resources/css/landing.css', 'resources/js/landing.js'])
    @endunless
</head>
<body class="gilas-landing">
    @include('components.footsteps')

    <main class="landing-stage" aria-label="{{ $restaurant?->name ?: 'گیلاس' }}"
          data-restaurant="{{ $restaurant?->name ?: 'گیلاس' }}">
        <div class="landing-copy">
            <span class="landing-kicker">{{ $restaurant?->name ?: 'گیلاس' }}</span>
            <h1>یک تجربه<br><strong>خوش‌طعم و متفاوت</strong></h1>
            <p>
                {{ $restaurant?->description ?: 'منو، فضا و حال‌وهوای گیلاس؛ همه‌چیز از یک لمس شروع می‌شود.' }}
            </p>
        </div>

        <div class="honey-detail" data-honey-detail>
            <span class="honey-detail__eyebrow">انتخاب تو</span>
            <h2 data-honey-title>{{ $honeycombItems[0]['title'] ?? 'منوی گیلاس' }}</h2>
            <p data-honey-text>{{ $honeycombItems[0]['text'] ?? 'طعم امروزت را از منوی گیلاس شروع کن.' }}</p>
            @if(!empty($honeycombItems))
                <a data-honey-action href="{{ $honeycombItems[0]['url'] }}">{{ $honeycombItems[0]['cta'] }}</a>
            @else
                <a data-honey-action href="{{ route('menu.index') }}">ورود به منو</a>
            @endif
        </div>

        <div id="container"
             class="honeycomb"
             aria-label="مسیرهای گیلاس"
             data-honeycomb='@json($honeycombItems, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)'>
            @php
                $honeycomb = [5, 6, 7, 8, 9, 8, 7, 6, 5];
            @endphp

            @foreach ($honeycomb as $columnIndex => $column)
                <div class="honeycomb__column" style="--column: {{ $columnIndex }};">
                    @for ($cellIndex = 1; $cellIndex <= $column; $cellIndex++)
                        @php
                            $flatIndex = $loop->parent->index * 9 + ($cellIndex - 1);
                            $target = $honeycombItems[$flatIndex % max(1, count($honeycombItems))] ?? [
                                'title' => 'گیلاس',
                                'text' => 'مسیرت را از همین‌جا شروع کن.',
                                'cta' => 'ورود',
                                'url' => route('menu.index'),
                                'icon' => '🍒',
                            ];
                        @endphp
                        <button
                            type="button"
                            class="hexagon"
                            style="--index: {{ $cellIndex }}; --icon: '{{ $target['icon'] }}';"
                            aria-label="{{ $target['title'] }}"
                            data-honey-index="{{ $flatIndex }}">
                        </button>
                    @endfor
                </div>
            @endforeach
        </div>

        <button id="switch"
                class="vision-switch"
                type="button"
                aria-label="تغییر حالت نمایش"
                aria-pressed="false">
            <span aria-hidden="true"></span>
        </button>

        <div class="landing-mark">G I L A S</div>

        @if($featuredItems->isNotEmpty())
            <section class="landing-featured" aria-label="انتخاب‌های امروز">
                <div class="landing-featured__heading">
                    <span>از منوی امروز</span>
                    <a href="{{ route('menu.index') }}">دیدن همه</a>
                </div>
                <div class="landing-featured__rail">
                    @foreach($featuredItems as $item)
                        <a href="{{ route('menu.item', ['slug' => $item->slug]) }}" class="landing-featured-card">
                            <span class="landing-featured-card__media">
                                @if($item->image_path)
                                    <img
                                        src="{{ IlluminateSupportFacadesStorage::url($item->image_path) }}"
                                        alt="{{ $item->name }}"
                                        width="320"
                                        height="240"
                                        loading="lazy"
                                        decoding="async">
                                @else
                                    <span aria-hidden="true">گ</span>
                                @endif
                            </span>
                            <span class="landing-featured-card__content">
                                <strong>{{ $item->name }}</strong>
                                <small>{{ number_format((int) $item->price) }} {{ $restaurant->currency ?? 'IRR' }}</small>
                            </span>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    </main>
</body>
</html>
