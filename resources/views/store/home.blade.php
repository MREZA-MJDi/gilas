@extends('layouts.store')

@section('title', ($restaurant?->name ?: 'گیلاس') . ' — تجربهٔ غذا و کافه')
@section('meta-description', $restaurant?->description ?: 'غذاها و نوشیدنی‌های منوی واقعی گیلاس را کشف کن و برای میزت هماهنگ شو.')
@section('canonical', route('home'))
@section('og-image', $restaurant?->cover_image_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($restaurant->cover_image_path) : '')

@php
    $coverImage = $restaurant?->cover_image_path
        ? \Illuminate\Support\Facades\Storage::disk('public')->url($restaurant->cover_image_path)
        : '';
    $heroItems = collect($featuredItems ?? [])->values()->map(function ($item) use ($restaurant, $coverImage) {
        $image = $item->image_path
            ? \Illuminate\Support\Facades\Storage::disk('public')->url($item->image_path)
            : $coverImage;

        return [
            'id' => $item->id,
            'name' => $item->name,
            'description' => $item->description ?: 'از انتخاب‌های منوی رستوران؛ با جزئیات واقعی و قیمت ثبت‌شده.',
            'url' => route('menu.item', ['slug' => $item->slug]),
            'image' => $image,
            'price' => number_format((int) $item->price) . ' ' . ($restaurant?->currency ?: 'تومان'),
            'label' => $item->category?->name ?: 'از منوی گیلاس',
            'theme' => '#284a3d',
        ];
    });
    $hero = $heroItems->first();
@endphp

@section('content')
<main class="store-main store-home gilas-store-home">
    <section class="home-hero" aria-labelledby="home-hero-title" data-home-hero style="--home-hero-base:#284a3d">
        <div class="home-hero__frame">
            <div class="home-hero__frame-brand">
                <a href="{{ route('home') }}" aria-label="صفحهٔ اصلی {{ $restaurant?->name ?: 'گیلاس' }}">
                    <strong>{{ $restaurant?->name ?: 'گیلاس' }}</strong><small>CAFE · KITCHEN · TABLE</small>
                </a>
            </div>
            <nav class="home-hero__nav" aria-label="دسترسی سریع">
                <a class="home-hero__nav-link home-hero__nav-link--current" href="{{ route('menu.index') }}">منوی رستوران</a>
                <a class="home-hero__nav-link" href="#home-categories">دسته‌بندی‌ها</a>
                <a class="home-hero__nav-link" href="{{ route('public.reservation') }}">رزرو میز</a>
                <a class="home-hero__nav-link" href="{{ route('public.location') }}">موقعیت</a>
            </nav>
            <div class="home-hero__top-actions">
                <a class="home-hero__icon-button" href="{{ route('public.reservation') }}" aria-label="رزرو میز" title="رزرو میز">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="5.5" width="16" height="15" rx="2"></rect><path d="M8 3.5v4M16 3.5v4M4 10h16"></path></svg>
                </a>
                <a class="home-hero__icon-button home-hero__cart" href="{{ route('cart.index') }}" aria-label="سبد سفارش میز" title="سبد سفارش">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 8h14l-1 12H6L5 8Z"></path><path d="M9 8V6a3 3 0 0 1 6 0v2"></path></svg><span aria-hidden="true">+</span>
                </a>
            </div>
            <div class="home-hero__copy">
                <div class="home-hero__slider-controls" aria-label="تغییر غذای پیشنهادی">
                    <button type="button" data-home-hero-prev aria-label="غذای قبلی">‹</button>
                    <button type="button" data-home-hero-next aria-label="غذای بعدی">›</button>
                </div>
                <p class="home-hero__eyebrow">یک خانه برای طعم‌های خوب</p>
                <h1 id="home-hero-title" class="home-hero__title" data-home-hero-title>طعم خوب،<br>جایی برای ماندن.</h1>
                <p class="home-hero__description" data-home-hero-description>{{ $restaurant?->description ?: 'غذا و نوشیدنی را از منوی رستوران انتخاب کن؛ سفارش میز از QR همان میز ثبت می‌شود.' }}</p>
                <a class="home-hero__cta" href="{{ $hero['url'] ?? route('menu.index') }}" data-home-hero-cta>
                    <span>{{ $hero ? 'جزئیات این انتخاب' : 'کشف منوی غذا' }}</span><span aria-hidden="true">↗</span>
                </a>
            </div>

            <div class="home-hero__image-stage" data-home-hero-stage data-base-image="{{ $hero['image'] ?? '' }}">
                <div class="gilas-home-plate">
                    <div class="gilas-home-steam" aria-hidden="true"><i></i><i></i><i></i></div>
                    <img data-home-hero-image src="{{ $hero['image'] ?? '' }}" alt="{{ $hero['name'] ?? ($restaurant?->name ?: 'گیلاس') }}" fetchpriority="high" decoding="async" @if(!($hero['image'] ?? null)) hidden @endif>
                    <div class="home-hero__image-missing" data-home-hero-image-missing @if($hero['image'] ?? null) hidden @endif><span>تصویر غذای واقعی از پنل منو نمایش داده می‌شود.</span></div>
                </div>
            </div>
            <div class="home-hero__image-caption" aria-live="polite">
                <span>از منوی واقعی گیلاس</span><strong data-home-hero-caption>{{ $hero['name'] ?? 'خانهٔ گیلاس' }}</strong>
            </div>
            <aside class="home-hero__price-panel" aria-label="اطلاعات انتخاب منو">
                <span class="home-hero__price-label">انتخاب امروز</span>
                <strong class="home-hero__price" data-home-hero-price>{{ $hero['price'] ?? 'منوی رستوران' }}</strong>
                <span class="home-hero__stock" data-home-hero-stock>قیمت از اطلاعات ثبت‌شدهٔ رستوران</span>
                @if($heroItems->count() > 1)
                    <div class="home-hero__variant-picker">
                        <span class="home-hero__variant-label">بین انتخاب‌ها جابه‌جا شو</span>
                        <div class="home-hero__variants" role="group" aria-label="انتخاب غذای پیشنهادی">
                            @foreach($heroItems as $index => $slide)
                                <button type="button" class="home-hero__variant{{ $index === 0 ? ' is-selected' : '' }}"
                                    data-home-hero-slide data-title="{{ $slide['name'] }}" data-description="{{ $slide['description'] }}"
                                    data-url="{{ $slide['url'] }}" data-price="{{ $slide['price'] }}" data-stock="از منوی رستوران"
                                    data-image="{{ $slide['image'] }}" data-image-alt="{{ $slide['name'] }}"
                                    data-label="{{ $slide['label'] }}" data-theme="{{ $slide['theme'] }}"
                                    aria-label="نمایش {{ $slide['name'] }}" title="{{ $slide['name'] }}"
                                    aria-pressed="{{ $index === 0 ? 'true' : 'false' }}">
                                    <span class="home-hero__variant-dot" aria-hidden="true"></span>
                                    <span class="home-hero__variant-initial" aria-hidden="true">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif
            </aside>
            <a class="home-hero__mini-product" href="{{ $hero['url'] ?? route('menu.index') }}" data-home-hero-thumbnail-link @if(!($hero['image'] ?? null)) hidden @endif>
                @if($hero && $hero['image'])
                    <img src="{{ $hero['image'] }}" alt="" loading="lazy" decoding="async" data-home-hero-thumbnail>
                @else
                    <img src="" alt="" loading="lazy" decoding="async" data-home-hero-thumbnail hidden>
                @endif
            </a>
            <div class="home-hero__bottom"><a href="#home-categories">دسته‌بندی‌ها</a><span aria-hidden="true">—</span><a href="{{ route('menu.index') }}">همهٔ منو</a></div>
        </div>
    </section>

    <section class="home-section home-categories" id="home-categories" aria-labelledby="home-categories-title">
        <div class="home-section__inner">
            <div class="home-section__heading">
                <div>
                    <p class="store-eyebrow">گیلاس / منوی رستوران</p>
                    <h2 id="home-categories-title">از کجا شروع کنیم؟</h2>
                    <p class="home-section__intro">دسته‌ها و آیتم‌ها از اطلاعات فعال دیتابیس نمایش داده می‌شوند.</p>
                </div>
                <a class="home-section__link" href="{{ route('menu.index') }}">دیدن همهٔ منو <span aria-hidden="true">←</span></a>
            </div>
            @if($categories->isNotEmpty())
                <div class="gilas-store-category-grid">
                    @foreach($categories as $category)
                        @php
                            $categoryItem = $category->items->first();
                            $categoryImage = $categoryItem?->image_path
                                ? \Illuminate\Support\Facades\Storage::disk('public')->url($categoryItem->image_path)
                                : null;
                        @endphp
                        <a class="gilas-store-category" href="{{ route('menu.category', ['slug' => $category->slug]) }}">
                            <span class="gilas-store-category__number">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            @if($categoryImage)
                                <img src="{{ $categoryImage }}" alt="" loading="lazy" decoding="async">
                            @else
                                <span class="gilas-store-category__placeholder" aria-hidden="true">{{ mb_substr($category->name, 0, 1) }}</span>
                            @endif
                            <span class="gilas-store-category__body"><strong>{{ $category->name }}</strong><small>{{ $category->items->count() }} آیتم فعال</small></span>
                            <span class="gilas-store-category__arrow" aria-hidden="true">↗</span>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="gilas-empty-state"><span class="gilas-empty-state__mark">✳</span><h3>منو در حال آماده شدن است.</h3><p>وقتی دسته‌ها و غذاهای واقعی در پنل رستوران ثبت شوند، این بخش به‌صورت خودکار پر می‌شود.</p></div>
            @endif
        </div>
    </section>

    <section class="home-section gilas-store-featured" aria-labelledby="gilas-store-featured-title">
        <div class="home-section__inner">
            <div class="home-section__heading">
                <div><p class="store-eyebrow">پیشنهادهای خانه</p><h2 id="gilas-store-featured-title">از منوی خانه</h2><p class="home-section__intro">غذاهای فعال و قابل مشاهده؛ بدون قیمت یا عکس ساختگی.</p></div>
                <a class="home-section__link" href="{{ route('menu.index') }}">مشاهدهٔ منو <span aria-hidden="true">←</span></a>
            </div>
            @if($featuredItems->isNotEmpty())
                <div class="public-product-grid">
                    @foreach($featuredItems as $item)
                        <x-menu-item-card :item="$item" :restaurant="$restaurant" :category-name="$item->category?->name" />
                    @endforeach
                </div>
            @else
                <div class="gilas-empty-inline">هنوز آیتمی در منوی عمومی منتشر نشده است.</div>
            @endif
        </div>
    </section>

    <section class="gilas-store-reservation">
        <div><p class="store-eyebrow">وقتِ خودت</p><h2>میزت را هماهنگ کن.<br><em>با خیال راحت برس.</em></h2><p>برای رزرو و هماهنگی زمان مراجعه، جزئیات را از صفحهٔ رزرو ببین.</p></div>
        <div class="gilas-store-reservation__actions">
            <a class="gilas-button gilas-button--dark" href="{{ route('public.reservation') }}">رزرو میز <span aria-hidden="true">↗</span></a>
            <a class="gilas-button gilas-button--line" href="{{ route('public.location') }}">دیدن موقعیت</a>
        </div>
    </section>
</main>
@endsection
