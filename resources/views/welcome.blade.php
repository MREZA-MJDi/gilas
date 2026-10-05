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
    <meta name="theme-color" content="#243331">
    <meta name="description" content="{{ $restaurant?->description ?: 'خانه گیلاسی؛ طعم، فضا و لحظه‌ای برای مکث.' }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <title>{{ $restaurant?->name ?: 'خانه گیلاسی' }}</title>
    @unless(app()->runningUnitTests())
        @vite(['resources/css/landing.css', 'resources/js/landing.js'])
    @endunless
</head>
<body class="home" data-home>
<header class="home-header">
    <div class="ui-shell home-header__inner">
        <a class="home-brand" href="{{ route('home') }}">
            <span class="home-brand__mark">
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" alt="" width="44" height="44">
                @else
                    <span>{{ mb_substr($restaurant?->name ?? 'گیلاس', 0, 1) }}</span>
                @endif
            </span>
            <span>
                <strong>{{ $restaurant?->name ?? 'خانه گیلاسی' }}</strong>
                <span>کافه‌ای برای مکث‌های خوب</span>
            </span>
        </a>
        <nav class="home-nav" aria-label="ناوبری">
            <a href="{{ route('public.experience') }}">تجربه</a>
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
    <section class="home-hero home-hero--gallery" data-home-hero>
        <div class="home-hero-gallery">
            <div class="home-hero-gallery__grid-shell">
                @if($heroItems->isNotEmpty())
                    <ul
                        class="grid"
                        style="--hero-grid-areas: {{ $heroGridAreas }};"
                        aria-label="غذاها و نوشیدنی‌های منتخب خانه گیلاسی"
                    >
                        @foreach($heroItems as $item)
                            @php
                                $imageUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($item->image_path);
                                $position = $loop->iteration;
                            @endphp
                            <li
                                class="pic pic--{{ $position }}"
                                style="--grid-area: pic{{ $position }}; background-image: url('{{ $imageUrl }}');"
                                data-num="{{ $position }}"
                                data-position="{{ $position - 1 }}"
                                data-image="{{ $imageUrl }}"
                                data-name="{{ $item->name }}"
                                data-price="{{ (int) $item->price }}"
                                aria-label="{{ $item->name }}"
                                role="button"
                                tabindex="{{ $position <= 5 ? '0' : '-1' }}"
                            ></li>
                        @endforeach
                    </ul>
                @else
                    <div class="hero-grid-empty">
                        <span class="ui-kicker">منوی امروز</span>
                        <h2>هنوز تصویر محصولی برای Hero انتخاب نشده.</h2>
                        <p>هر محصول فعال و قابل‌عرضه که تصویر داشته باشد، از دیتابیس وارد این بخش می‌شود.</p>
                    </div>
                @endif
            </div>

            <div class="home-hero-gallery__copy" data-home-reveal>
                <span class="home-hero-gallery__eyebrow">خانه گیلاسی · انتخاب‌های امروز</span>
                <h1>طعم خوب،<br>با عجله نمی‌آید.</h1>
                <p>{{ $restaurant?->description ?: 'غذا، نوشیدنی و دسرهای واقعی منوی خانه گیلاسی؛ از دیتابیس، بدون تصویرهای ساختگی یا خارجی.' }}</p>
                <div class="home-hero-gallery__actions">
                    <a class="ui-button ui-button--warm" href="{{ route('menu.index') }}">منو و قیمت‌ها</a>
                    @if($restaurant?->settings?->reservation_enabled)
                        <a class="ui-button ui-button--ghost" href="{{ route('public.reservation') }}">رزرو میز</a>
                    @endif
                </div>
            </div>

            <span class="hero-grid-tip">← برای دیدن جزئیات روی تصویر بزن →</span>
        </div>

        <div
            class="hero-grid-view"
            data-state="closed"
            data-is-vertical="0"
            aria-hidden="true"
            role="dialog"
            aria-modal="true"
            aria-label="نمایش تصویر محصول"
        >
            <div class="hero-grid-view__container">
                <div class="hero-grid-view__img-wrapper">
                    <img src="" alt="" class="img">
                    <div class="hero-grid-view__caption">
                        <strong data-view-name></strong>
                        <span data-view-price></span>
                    </div>
                </div>

                <div class="arrows" aria-hidden="true">
                    <button type="button" class="arrow arrow--prev" aria-label="محصول قبلی"></button>
                    <button type="button" class="arrow arrow--next" aria-label="محصول بعدی"></button>
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
                    <div>
                        <em>01</em>
                        <strong class="block mt-3">منو و سفارش</strong>
                        <span class="block mt-2">غذاها، نوشیدنی‌ها، انتخاب‌های قابل شخصی‌سازی و قیمت واقعی.</span>
                    </div>
                    <span>کشف منو ←</span>
                </a>

                <a class="home-action" href="{{ route('public.reservation') }}" data-home-reveal>
                    <div>
                        <em>02</em>
                        <strong class="block mt-3">رزرو میز</strong>
                        <span class="block mt-2">برای قرار بعدی، قبل از رسیدن میزت را هماهنگ کن.</span>
                    </div>
                    <span>هماهنگی میز ←</span>
                </a>

                <a class="home-action" href="{{ route('public.location') }}" data-home-reveal>
                    <div>
                        <em>03</em>
                        <strong class="block mt-3">پیدا کردن ما</strong>
                        <span class="block mt-2">{{ $restaurant?->address ?: 'آدرس از تنظیمات رستوران خوانده می‌شود.' }}</span>
                    </div>
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
