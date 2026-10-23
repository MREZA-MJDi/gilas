@extends('layouts.store')

@section('title', ($restaurant?->name ?: 'گیلاس') . ' — جایی برای طعم‌های خوب')
@section('meta-description', $restaurant?->description ?: 'غذاهای واقعی منوی گیلاس را کشف کن؛ یک مکث خوش‌طعم، یک میز گرم و انتخاب‌هایی از آشپزخانهٔ ما.')
@section('canonical', route('home'))
@section('og-image', $restaurant?->cover_image_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($restaurant->cover_image_path) : '')

@php
    $coverImage = $restaurant?->cover_image_path
        ? \Illuminate\Support\Facades\Storage::disk('public')->url($restaurant->cover_image_path)
        : '';

    $heroSlides = collect($featuredItems ?? [])->take(4)->map(function ($item) use ($restaurant, $coverImage) {
        $image = $item->image_path
            ? \Illuminate\Support\Facades\Storage::disk('public')->url($item->image_path)
            : $coverImage;

        return [
            'id' => $item->id,
            'name' => $item->name,
            'description' => $item->description ?: 'از انتخاب‌های آشپزخانهٔ گیلاس؛ با مواد اولیه و جزئیاتی که در خود رستوران ثبت شده‌اند.',
            'category' => $item->category?->name ?: 'از منوی گیلاس',
            'image' => $image,
            'price' => number_format((int) $item->price) . ' ' . ($restaurant?->currency ?: 'تومان'),
            'url' => route('menu.item', ['slug' => $item->slug]),
        ];
    })->values();

    if ($heroSlides->isEmpty()) {
        $heroSlides->push([
            'id' => null,
            'name' => $restaurant?->name ?: 'خانهٔ گیلاس',
            'description' => $restaurant?->description ?: 'منو را کشف کن، برای میزت هماهنگ شو و جزئیات خانهٔ گیلاس را از نزدیک ببین.',
            'category' => 'کافه · آشپزخانه · میز',
            'image' => $coverImage,
            'price' => 'منوی رستوران',
            'url' => route('menu.index'),
        ]);
    }
@endphp

@section('content')
<main class="gilas-home">
    <section class="gilas-hero" data-gilas-hero aria-label="معرفی غذاهای منوی گیلاس">
        <div class="gilas-hero__slides">
            @foreach($heroSlides as $slide)
                <article class="gilas-hero__slide{{ $loop->first ? ' is-active' : '' }}"
                         data-hero-slide
                         aria-hidden="{{ $loop->first ? 'false' : 'true' }}"
                         @unless($loop->first) inert @endunless>
                    <div class="gilas-hero__media">
                        @if(filled($slide['image']))
                            <img src="{{ $slide['image'] }}" alt="{{ $slide['name'] }}"
                                 width="1800" height="1200"
                                 @if($loop->first) fetchpriority="high" @else loading="lazy" @endif
                                 decoding="async">
                        @else
                            <div class="gilas-hero__image-placeholder" aria-hidden="true">
                                <span class="gilas-hero__placeholder-ring"></span>
                                <span class="gilas-hero__placeholder-mark">گ.</span>
                            </div>
                        @endif
                    </div>
                    <div class="gilas-hero__shade" aria-hidden="true"></div>
                    <div class="gilas-hero__frame" aria-hidden="true"><span></span><span></span><span></span><span></span></div>
                    <div class="gilas-hero__content">
                        <p class="gilas-hero__eyebrow"><span class="gilas-hero__eyebrow-line"></span> CAFE / KITCHEN / TABLE</p>
                        <h1 class="gilas-hero__title">یک مکثِ خوش‌طعم،<br><span>{{ $slide['name'] }}</span></h1>
                        <p class="gilas-hero__description">{{ $slide['description'] }}</p>
                        <div class="gilas-hero__actions">
                            <a class="gilas-hero__button gilas-hero__button--light" href="{{ $slide['url'] }}">کشف این انتخاب <span aria-hidden="true">↗</span></a>
                            <a class="gilas-hero__button gilas-hero__button--ghost" href="{{ route('public.reservation') }}">رزرو میز</a>
                        </div>
                        <div class="gilas-hero__caption">
                            <span>{{ $slide['category'] }}</span>
                            <strong>{{ $slide['name'] }}</strong>
                            <small>{{ $slide['price'] }}</small>
                        </div>
                    </div>
                    <span class="gilas-hero__index" aria-hidden="true">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }} <i></i> {{ str_pad((string) $heroSlides->count(), 2, '0', STR_PAD_LEFT) }}</span>
                </article>
            @endforeach
        </div>

        <div class="gilas-hero__rail">
            <span class="gilas-hero__rail-label">TASTE / TABLE / FEELING</span>
            <div class="gilas-hero__progress" aria-hidden="true"><span data-hero-progress></span></div>
            @if($heroSlides->count() > 1)
                <div class="gilas-hero__controls" aria-label="کنترل اسلایدهای خانه">
                    <button type="button" data-hero-prev aria-label="اسلاید قبلی">→</button>
                    <div class="gilas-hero__dots" role="tablist" aria-label="انتخاب غذای پیشنهادی">
                        @foreach($heroSlides as $slide)
                            <button type="button" data-hero-dot role="tab"
                                    aria-label="اسلاید {{ $loop->iteration }}"
                                    aria-selected="{{ $loop->first ? 'true' : 'false' }}"
                                    class="{{ $loop->first ? 'is-active' : '' }}"></button>
                        @endforeach
                    </div>
                    <button type="button" data-hero-next aria-label="اسلاید بعدی">←</button>
                </div>
            @endif
            <a class="gilas-hero__scroll" href="#home-categories"><span>برای کشف بیشتر اسکرول کن</span><i aria-hidden="true"></i></a>
        </div>
    </section>

    <section class="gilas-home-section gilas-home-section--categories" id="home-categories" aria-labelledby="home-categories-title">
        <div class="gilas-container">
            <header class="gilas-section-head" data-gilas-reveal>
                <div>
                    <p class="gilas-kicker">01 / دسته‌بندی‌های منو</p>
                    <h2 id="home-categories-title" class="gilas-section-title">از کجا شروع کنیم؟<br><em>انتخاب با توست.</em></h2>
                </div>
                <a class="gilas-text-link" href="{{ route('menu.index') }}">دیدن همهٔ منو <span aria-hidden="true">↗</span></a>
            </header>

            @if($categories->isNotEmpty())
                <div class="gilas-category-grid">
                    @foreach($categories as $category)
                        @php
                            $categoryItem = $category->items->first();
                            $categoryImage = $categoryItem?->image_path
                                ? \Illuminate\Support\Facades\Storage::disk('public')->url($categoryItem->image_path)
                                : null;
                        @endphp
                        <a class="gilas-category" href="{{ route('menu.category', ['slug' => $category->slug]) }}"
                           data-gilas-reveal style="--category-index: {{ $loop->iteration }};">
                            <span class="gilas-category__number">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="gilas-category__media">
                                @if($categoryImage)
                                    <img src="{{ $categoryImage }}" alt="{{ $category->name }}" width="640" height="760" loading="lazy" decoding="async">
                                @else
                                    <span class="gilas-category__placeholder" aria-hidden="true">{{ mb_substr($category->name, 0, 1) }}</span>
                                @endif
                                <span class="gilas-category__veil" aria-hidden="true"></span>
                                <span class="gilas-category__glass">
                                    <strong>{{ $category->name }}</strong>
                                    <span>{{ $category->items->count() }} انتخاب <b aria-hidden="true">↗</b></span>
                                </span>
                            </span>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="gilas-empty" data-gilas-reveal>
                    <span class="gilas-empty__mark">گ.</span>
                    <h3>منو در حال آماده‌شدن است.</h3>
                    <p>وقتی دسته‌ها و غذاهای واقعی در پنل منتشر شوند، این بخش خودکار کامل می‌شود.</p>
                </div>
            @endif
        </div>
    </section>

    <section class="gilas-home-section gilas-home-section--featured" aria-labelledby="gilas-featured-title">
        <div class="gilas-container">
            <header class="gilas-section-head" data-gilas-reveal>
                <div>
                    <p class="gilas-kicker">02 / منتخب آشپزخانه</p>
                    <h2 id="gilas-featured-title" class="gilas-section-title">از منوی <em>خانه</em></h2>
                    <p class="gilas-section-description">آیتم‌های فعال، عکس‌های واقعی و قیمت‌های ثبت‌شده؛ بدون محتوای نمایشی.</p>
                </div>
                <a class="gilas-text-link" href="{{ route('menu.index') }}">دیدن همهٔ انتخاب‌ها <span aria-hidden="true">↗</span></a>
            </header>
            @if($featuredItems->isNotEmpty())
                <div class="public-product-grid gilas-featured-grid">
                    @foreach($featuredItems as $item)
                        <x-menu-item-card :item="$item" :restaurant="$restaurant" :category-name="$item->category?->name" />
                    @endforeach
                </div>
            @else
                <div class="gilas-empty" data-gilas-reveal>
                    <h3>هنوز آیتم فعالی برای نمایش منتشر نشده است.</h3>
                    <p>غذاها و نوشیدنی‌ها پس از انتشار در پنل رستوران اینجا دیده می‌شوند.</p>
                    <a class="gilas-button gilas-button--dark" href="{{ route('menu.index') }}">رفتن به منو ↗</a>
                </div>
            @endif
        </div>
    </section>

    <section class="gilas-manifesto" aria-labelledby="gilas-manifesto-title" data-gilas-reveal>
        <div class="gilas-container gilas-manifesto__inner">
            <p class="gilas-kicker">A PLACE TO STAY / EAT WELL</p>
            <h2 id="gilas-manifesto-title">کمتر عجله کن،<br><em>بیشتر مزه کن.</em></h2>
            <p>{{ $restaurant?->description ?: 'برای یک گفت‌وگوی خوب، یک بشقاب دل‌چسب و لحظه‌ای دور از شلوغی روزمره.' }}</p>
            <div class="gilas-manifesto__actions">
                <a class="gilas-hero__button gilas-hero__button--light" href="{{ route('public.reservation') }}">هماهنگی میز <span aria-hidden="true">↗</span></a>
                <a class="gilas-manifesto__link" href="{{ route('public.story') }}">داستان خانهٔ گیلاس <span aria-hidden="true">←</span></a>
            </div>
            <span class="gilas-manifesto__glyph" aria-hidden="true">گ.</span>
        </div>
    </section>

    <section class="gilas-home-section gilas-home-section--visit" aria-labelledby="gilas-visit-title">
        <div class="gilas-container gilas-visit" data-gilas-reveal>
            <div class="gilas-visit__copy">
                <p class="gilas-kicker">03 / یک قرار خوب</p>
                <h2 id="gilas-visit-title">میزت را هماهنگ کن.<br><em>با خیال راحت برس.</em></h2>
                <p>برای رزرو و اطلاع از مسیر، از جزئیات ثبت‌شدهٔ رستوران استفاده کن.</p>
            </div>
            <div class="gilas-visit__actions">
                <a class="gilas-hero__button gilas-hero__button--dark" href="{{ route('public.reservation') }}">رزرو میز <span aria-hidden="true">↗</span></a>
                <a class="gilas-hero__button gilas-hero__button--outline" href="{{ route('public.location') }}">آدرس و مسیریابی</a>
            </div>
        </div>
    </section>
</main>
@endsection
