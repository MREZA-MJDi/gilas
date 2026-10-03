<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#0b090d">
    <meta name="description" content="{{ $restaurant?->description ?: 'گیلاس — یک تجربه خوش‌طعم و متفاوت.' }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <title>{{ $restaurant?->name ?: 'گیلاس' }}</title>
    @unless(app()->environment('testing'))
        @vite(['resources/css/landing.css', 'resources/js/landing.js'])
    @endunless
</head>
<body class="gilas-landing">
    @include('components.footsteps')

    <main class="gilas-home">
        <section class="landing-stage" aria-label="{{ $restaurant?->name ?: 'گیلاس' }}">
            <div class="landing-copy">
                <span class="landing-kicker">{{ $restaurant?->name ?: 'گیلاس' }}</span>
                <h1>یک تجربه<br><strong>خوش‌طعم و متفاوت</strong></h1>
                <p>{{ $restaurant?->description ?: 'منو، فضا و حال‌وهوای گیلاس؛ همه‌چیز از یک لمس شروع می‌شود.' }}</p>
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
                    $flatIndex = 0;
                @endphp

                @foreach ($honeycomb as $columnIndex => $column)
                    <div class="honeycomb__column" style="--column: {{ $columnIndex }};">
                        @for ($cellIndex = 1; $cellIndex <= $column; $cellIndex++)
                            @php
                                $targetIndex = $flatIndex++;
                                $target = $honeycombItems[$targetIndex % max(1, count($honeycombItems))] ?? [
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
                                data-honey-index="{{ $targetIndex }}">
                            </button>
                        @endfor
                    </div>
                @endforeach
            </div>

            <button id="switch" class="vision-switch" type="button" aria-label="تغییر حالت نمایش" aria-pressed="false">
                <span aria-hidden="true"></span>
            </button>

            <div class="landing-mark" aria-hidden="true">G I L A S</div>
        </section>

        <section class="landing-section landing-section--featured" aria-labelledby="featured-title">
            <div class="landing-section__inner">
                <div class="landing-section__heading">
                    <div>
                        <span class="landing-section__eyebrow">Today at {{ $restaurant?->name ?: 'گیلاس' }}</span>
                        <h2 id="featured-title">چیزهایی که امروز<br><strong>دلمان می‌خواهد.</strong></h2>
                    </div>
                    <a class="landing-section__link" href="{{ route('menu.index') }}">تمام منو <span aria-hidden="true">←</span></a>
                </div>

                <div class="landing-product-rail">
                    @forelse($featuredItems as $featured)
                        @php $item = $featured['item']; $category = $featured['category']; @endphp
                        <a class="landing-product" href="{{ route('menu.item', ['slug' => $item->slug]) }}">
                            <div class="landing-product__media">
                                @if($item->image_path)
                                    <img
                                        src="{{ \Illuminate\Support\Facades\Storage::url($item->image_path) }}"
                                        alt="{{ $item->name }}"
                                        loading="lazy"
                                        decoding="async"
                                        width="720"
                                        height="720">
                                @else
                                    <span aria-hidden="true">گ</span>
                                @endif
                            </div>
                            <div class="landing-product__body">
                                <div>
                                    <span>{{ $category->name }}</span>
                                    <h3>{{ $item->name }}</h3>
                                </div>
                                <strong>{{ number_format((int) $item->price) }} {{ $restaurant?->currency ?: 'IRR' }}</strong>
                            </div>
                        </a>
                    @empty
                        <div class="landing-empty">
                            <span class="landing-section__eyebrow">Menu</span>
                            <h3>منوی گیلاس آماده‌ی پر شدن است.</h3>
                            <p>آیتم‌ها را از داشبورد وارد کن تا این بخش خودش زنده شود.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="landing-section landing-section--categories" aria-labelledby="categories-title">
            <div class="landing-section__inner">
                <div class="landing-section__heading">
                    <div>
                        <span class="landing-section__eyebrow">Explore</span>
                        <h2 id="categories-title">هر حال‌وهوایی،<br><strong>یک انتخاب دارد.</strong></h2>
                    </div>
                </div>

                <div class="landing-category-grid">
                    @forelse($menu as $category)
                        <a class="landing-category" href="{{ route('menu.category', ['slug' => $category->slug]) }}">
                            <span class="landing-category__index">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            <div>
                                <h3>{{ $category->name }}</h3>
                                <p>{{ $category->description ?: 'انتخاب‌های این دسته را کشف کن.' }}</p>
                            </div>
                            <span class="landing-category__arrow" aria-hidden="true">↗</span>
                        </a>
                    @empty
                        <p class="landing-empty">هنوز دسته‌ای برای منو تعریف نشده.</p>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="landing-section landing-section--experience" aria-labelledby="experience-title">
            <div class="landing-section__inner landing-experience">
                <div class="landing-experience__copy">
                    <span class="landing-section__eyebrow">The Gilas feeling</span>
                    <h2 id="experience-title">فقط برای خوردن نیست.<br><strong>برای ماندن هم هست.</strong></h2>
                    <p>{{ $restaurant?->description ?: 'گیلاس جایی برای یک قهوه‌ی خوب، یک گفت‌وگوی طولانی و چند دقیقه دور شدن از شلوغی است.' }}</p>
                    <a class="landing-button" href="{{ route('public.experience') }}">کشف تجربه</a>
                </div>
                @if($restaurant?->cover_image_path)
                    <div class="landing-experience__media">
                        <img
                            src="{{ \Illuminate\Support\Facades\Storage::url($restaurant->cover_image_path) }}"
                            alt="{{ $restaurant->name }}"
                            loading="lazy"
                            decoding="async"
                            width="1200"
                            height="900">
                    </div>
                @endif
            </div>
        </section>

        <section class="landing-section landing-section--cta" aria-labelledby="cta-title">
            <div class="landing-section__inner landing-cta">
                <div>
                    <span class="landing-section__eyebrow">Come by</span>
                    <h2 id="cta-title">امشب،<br><strong>گیلاس می‌بینمت؟</strong></h2>
                </div>
                <div class="landing-cta__actions">
                    @if($restaurant?->settings?->reservation_enabled)
                        <a class="landing-button" href="{{ route('public.reservation') }}">رزرو میز</a>
                    @endif
                    <a class="landing-button landing-button--ghost" href="{{ route('public.location') }}">مسیریابی</a>
                    <a class="landing-text-link" href="{{ route('menu.index') }}">یا مستقیم برو سراغ منو ←</a>
                </div>
            </div>
        </section>

        <footer class="landing-footer">
            <div class="landing-footer__inner">
                <strong>{{ $restaurant?->name ?: 'گیلاس' }}</strong>
                <span>{{ $restaurant?->address ?: 'یک جای خوب برای یک توقف خوب.' }}</span>
                <a href="{{ route('public.story') }}">داستان گیلاس</a>
            </div>
        </footer>
    </main>
</body>
</html>
