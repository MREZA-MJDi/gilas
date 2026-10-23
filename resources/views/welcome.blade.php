@extends('layouts.customer')

@section('title', ($restaurant?->name ?: 'خانه گیلاسی') . ' — تجربهٔ غذا و کافه')
@section('description', $restaurant?->description ?: 'منوی خانه گیلاسی را ببین، غذاها را کشف کن و برای میزت هماهنگ شو.')

@php
    $heroImagePath = $restaurant?->cover_image_path ?: $featuredItems->first()?->image_path;
    $heroImageUrl = $heroImagePath
        ? \Illuminate\Support\Facades\Storage::disk('public')->url($heroImagePath)
        : null;
    $heroImageAlt = $restaurant?->cover_image_path
        ? ($restaurant?->name ?: 'خانه گیلاسی')
        : ($featuredItems->first()?->name ?: ($restaurant?->name ?: 'خانه گیلاسی'));
@endphp

@section('content')
<main class="gilas-home">
    <section class="gilas-hero">
        <div class="gilas-hero__copy">
            <span class="gilas-eyebrow"><span class="gilas-eyebrow__line"></span> یک خانه برای طعم‌های خوب</span>
            <h1>طعم خوب،<br><em>جایی برای ماندن.</em></h1>
            <p>{{ $restaurant?->description ?: 'منو را کشف کن، برای میزت هماهنگ شو و جزئیات خانه گیلاسی را از نزدیک ببین.' }}</p>
            <div class="gilas-hero__actions">
                <a class="gilas-button gilas-button--light" href="{{ route('menu.index') }}">کشف منوی غذا <span aria-hidden="true">←</span></a>
                <a class="gilas-button gilas-button--outline" href="{{ route('public.reservation') }}">رزرو و هماهنگی میز</a>
            </div>
            <div class="gilas-hero__meta">
                <span><i aria-hidden="true"></i> منوی واقعی رستوران</span>
                <span><i aria-hidden="true"></i> سفارش مستقیم از میز</span>
            </div>
        </div>
        <div class="gilas-hero__visual">
            @if($heroImageUrl)
                <img src="{{ $heroImageUrl }}" alt="{{ $heroImageAlt }}" width="1200" height="1500" fetchpriority="high" decoding="async">
                <div class="gilas-hero__visual-shade" aria-hidden="true"></div>
            @else
                <div class="gilas-hero__image-empty" aria-label="تصویر اصلی هنوز از پنل رستوران ثبت نشده است">
                    <span class="gilas-hero__image-empty-mark">{{ mb_substr($restaurant?->name ?: 'گ', 0, 1) }}</span>
                    <span>تصویر اصلی را از تنظیمات رستوران اضافه کنید</span>
                </div>
            @endif
            <div class="gilas-hero__image-note">
                <span class="gilas-hero__note-dot"></span>
                <span><small>از خانهٔ ما</small><strong>{{ $restaurant?->name ?: 'خانه گیلاسی' }}</strong></span>
            </div>
            @if($featuredItems->isNotEmpty())
                <a class="gilas-hero__featured" href="{{ route('menu.item', ['slug' => $featuredItems->first()->slug]) }}">
                    <span class="gilas-hero__featured-label">از منوی رستوران</span>
                    <strong>{{ $featuredItems->first()->name }}</strong>
                    <span class="gilas-hero__featured-price">{{ number_format((int) $featuredItems->first()->price) }} {{ $restaurant?->currency ?: 'تومان' }}</span>
                    <span class="gilas-hero__featured-arrow" aria-hidden="true">↗</span>
                </a>
            @endif
        </div>
    </section>

    <section class="gilas-home-intro">
        <div>
            <span class="gilas-eyebrow"><span class="gilas-eyebrow__line"></span> آشنایی با خانه</span>
            <h2>انتخاب را ساده می‌کنیم؛<br><em>جزئیات را به تو می‌سپاریم.</em></h2>
        </div>
        <p>غذاها و نوشیدنی‌های فعال را از منوی واقعی ببین. برای جزئیات هر آیتم وارد صفحه‌اش شو یا اگر سر میز نشسته‌ای، QR همان میز را اسکن کن و سفارشت را مستقیم ثبت کن.</p>
    </section>

    <section class="gilas-featured-section" aria-labelledby="gilas-featured-title">
        <div class="gilas-section-heading">
            <div>
                <span class="gilas-eyebrow"><span class="gilas-eyebrow__line"></span> منوی رستوران</span>
                <h2 id="gilas-featured-title">از منوی خانه</h2>
            </div>
            <a class="gilas-text-link" href="{{ route('menu.index') }}">دیدن همهٔ دسته‌ها <span aria-hidden="true">←</span></a>
        </div>

        @if($featuredItems->isNotEmpty())
            <div class="gilas-home-dishes">
                @foreach($featuredItems as $item)
                    @php
                        $itemImageUrl = $item->image_path
                            ? \Illuminate\Support\Facades\Storage::disk('public')->url($item->image_path)
                            : null;
                    @endphp
                    <article class="gilas-home-dish">
                        <a class="gilas-home-dish__media" href="{{ route('menu.item', ['slug' => $item->slug]) }}" aria-label="جزئیات {{ $item->name }}">
                            @if($itemImageUrl)
                                <img src="{{ $itemImageUrl }}" alt="{{ $item->name }}" width="900" height="740" loading="lazy" decoding="async">
                            @else
                                <span class="gilas-home-dish__no-image">{{ mb_substr($item->name, 0, 1) }}</span>
                            @endif
                            <span class="gilas-home-dish__open" aria-hidden="true">↗</span>
                        </a>
                        <div class="gilas-home-dish__body">
                            <span class="gilas-home-dish__category">{{ $item->category?->name ?: 'از منوی خانه' }}</span>
                            <h3><a href="{{ route('menu.item', ['slug' => $item->slug]) }}">{{ $item->name }}</a></h3>
                            @if($item->description)
                                <p>{{ \Illuminate\Support\Str::limit($item->description, 92) }}</p>
                            @endif
                            <div class="gilas-home-dish__footer">
                                <strong>{{ number_format((int) $item->price) }} <small>{{ $restaurant?->currency ?: 'تومان' }}</small></strong>
                                <a href="{{ route('menu.item', ['slug' => $item->slug]) }}">جزئیات غذا <span aria-hidden="true">←</span></a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="gilas-empty-state">
                <span class="gilas-empty-state__mark">✳</span>
                <h3>منو در حال آماده شدن است.</h3>
                <p>وقتی غذاها و نوشیدنی‌ها را در پنل منتشر کنی، همین‌جا به‌صورت خودکار نمایش داده می‌شوند.</p>
                <a class="gilas-button gilas-button--dark" href="{{ route('menu.index') }}">رفتن به منو</a>
            </div>
        @endif
    </section>

    <section class="gilas-experience-band">
        <div class="gilas-experience-band__copy">
            <span class="gilas-eyebrow"><span class="gilas-eyebrow__line"></span> وقتِ خودت</span>
            <h2>میزت را هماهنگ کن.<br><em>با خیال راحت برس.</em></h2>
            <p>
                @if($restaurant?->settings?->reservation_enabled)
                    برای هماهنگی رزرو، ظرفیت و زمان مراجعه از مسیر اختصاصی رزرو استفاده کن.
                @else
                    برای اطلاع از شرایط مراجعه و ظرفیت، اطلاعات تماس و موقعیت رستوران را ببین.
                @endif
            </p>
            <div class="gilas-experience-band__actions">
                <a class="gilas-button gilas-button--light" href="{{ route('public.reservation') }}">رفتن به صفحهٔ رزرو <span aria-hidden="true">←</span></a>
                <a class="gilas-button gilas-button--outline" href="{{ route('public.location') }}">دیدن موقعیت</a>
            </div>
        </div>
        <div class="gilas-experience-band__side">
            <span class="gilas-experience-band__number">01</span>
            <div class="gilas-experience-band__rule"></div>
            <span class="gilas-experience-band__caption">منو · میز · تجربه</span>
        </div>
    </section>

    <section class="gilas-home-links" aria-label="درباره خانه گیلاسی">
        <a href="{{ route('public.experience') }}"><span>01 / تجربه</span><strong>تجربهٔ خانه</strong><i aria-hidden="true">↗</i></a>
        <a href="{{ route('public.story') }}"><span>02 / داستان</span><strong>داستان ما</strong><i aria-hidden="true">↗</i></a>
        <a href="{{ route('public.location') }}"><span>03 / مسیر</span><strong>آدرس و مسیریابی</strong><i aria-hidden="true">↗</i></a>
    </section>
</main>
@endsection
