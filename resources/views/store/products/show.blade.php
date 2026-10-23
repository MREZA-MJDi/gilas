@extends('layouts.store')

@section('title', $item->name . ' — ' . $restaurant->name)
@section('meta-description', $item->description ?: 'جزئیات و قیمت ثبت‌شدهٔ ' . $item->name . ' از منوی ' . $restaurant->name)
@section('canonical', route('menu.item', ['slug' => $item->slug]))
@section('og-type', 'product')
@section('og-image', $item->image_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($item->image_path) : '')

@section('content')
<main class="store-main gilas-item-page gilas-page-wrap">
    <nav class="commerce-breadcrumbs" aria-label="مسیر صفحه">
        <a href="{{ route('home') }}">خانه</a><span>/</span><a href="{{ route('menu.index') }}">منو</a><span>/</span><span>{{ $item->name }}</span>
    </nav>
    <article class="gilas-item-detail">
        <div class="gilas-item-detail__media">
            @if($item->image_path)
                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($item->image_path) }}" alt="{{ $item->name }}" width="1200" height="1200" loading="eager" decoding="async">
            @else
                <div class="gilas-item-detail__placeholder"><span>{{ mb_substr($item->name, 0, 1) }}</span><small>تصویر این غذا هنوز از پنل ثبت نشده است</small></div>
            @endif
            <span class="gilas-item-detail__media-tag">{{ $item->category?->name ?: 'منوی گیلاس' }}</span>
        </div>
        <div class="gilas-item-detail__body">
            <span class="gilas-eyebrow"><span class="gilas-eyebrow__line"></span> جزئیات از منوی واقعی</span>
            <h1>{{ $item->name }}</h1>
            @if($item->description)<p class="gilas-item-detail__description">{{ $item->description }}</p>@endif
            <div class="gilas-item-price"><span>قیمت پایه</span><strong>{{ number_format((int) $item->price) }} <small>{{ $restaurant->currency }}</small></strong></div>
            @if($item->variants->isNotEmpty())
                <section class="gilas-item-options">
                    <h2>اندازه‌ها و انتخاب‌ها</h2>
                    <div class="gilas-item-options__list">
                        @foreach($item->variants as $variant)
                            <div><span>{{ $variant->name }}</span><strong>{{ number_format((int) $variant->price) }} <small>{{ $restaurant->currency }}</small></strong></div>
                        @endforeach
                    </div>
                </section>
            @endif
            @if($item->options->isNotEmpty())
                <section class="gilas-item-options">
                    <h2>شخصی‌سازی غذا</h2>
                    <div class="gilas-item-options__list">
                        @foreach($item->options as $option)
                            <div class="gilas-item-options__option"><strong>{{ $option->name }}</strong><span>{{ $option->values->pluck('name')->join(' · ') ?: 'گزینه‌ای ثبت نشده است' }}</span></div>
                        @endforeach
                    </div>
                </section>
            @endif
            <div class="gilas-item-detail__actions">
                <a class="gilas-button gilas-button--dark" href="{{ route('menu.index') }}">ادامهٔ دیدن منو <span aria-hidden="true">←</span></a>
                @if($restaurant->settings?->reservation_enabled)<a class="gilas-button gilas-button--line" href="{{ route('public.reservation') }}">رزرو میز</a>@endif
            </div>
            <p class="gilas-item-detail__hint">برای ثبت سفارش و انتخاب گزینه‌ها، QR میز را اسکن کن تا سفارش به همان میز متصل شود.</p>
        </div>
    </article>
</main>
@endsection
