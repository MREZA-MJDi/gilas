@extends('layouts.customer')

@section('title', $restaurant->name . ' — منو')
@section('description', 'منوی آنلاین ' . $restaurant->name)

@section('content')
<header class="customer-header">
    <div class="ui-shell customer-header__inner">
        <a class="customer-brand" href="{{ route('home') }}">
            <span class="customer-brand__mark">
                @if($restaurant->logo_path)
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($restaurant->logo_path) }}" alt="" width="42" height="42">
                @else
                    <span>{{ mb_substr($restaurant->name, 0, 1) }}</span>
                @endif
            </span>
            <span>
                <strong>{{ $restaurant->name }}</strong>
                <small>منوی آنلاین</small>
            </span>
        </a>
        <div class="customer-header__actions">
            <a href="{{ route('home') }}">خانه</a>
            @if($restaurant->settings?->reservation_enabled)
                <a href="{{ route('public.reservation') }}">رزرو</a>
            @endif
        </div>
    </div>
</header>

<main class="ui-shell public-menu">
    <section class="public-menu__intro" data-home-reveal>
        <div>
            <span class="ui-kicker">منوی خانه گیلاسی</span>
            <h1>انتخاب خوب،<br>از یک نگاه شروع می‌شود.</h1>
            <p>{{ $restaurant->description ?: 'طعم‌ها، اندازه‌ها و انتخاب‌های واقعی امروز را ببین و با خیال راحت انتخاب کن.' }}</p>
        </div>
        @if($restaurant->cover_image_path)
            <div class="public-menu__hero-image">
                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($restaurant->cover_image_path) }}"
                     alt="{{ $restaurant->name }}"
                     width="1200" height="750" loading="eager" decoding="async">
            </div>
        @endif
    </section>

    <nav class="public-menu__filters" aria-label="دسته‌های منو">
        <a href="{{ route('menu.index') }}" class="{{ blank($selectedSlug) ? 'is-active' : '' }}">همه</a>
        @foreach($menu as $category)
            <a href="{{ route('menu.category', ['slug' => $category->slug]) }}"
               class="{{ $selectedSlug === $category->slug ? 'is-active' : '' }}">
                {{ $category->name }}
            </a>
        @endforeach
    </nav>

    <div class="public-menu__sections">
        @forelse($menu as $category)
            <section class="public-menu-category" id="category-{{ $category->slug }}">
                <div class="public-menu-category__heading">
                    <div>
                        <span class="ui-kicker">دسته</span>
                        <h2>{{ $category->name }}</h2>
                    </div>
                    <span>{{ $category->items->count() }} آیتم</span>
                </div>

                <div class="public-product-grid">
                    @foreach($category->items as $item)
                        <x-menu-item-card :item="$item" :restaurant="$restaurant" :category-name="$category->name" />
                    @endforeach
                </div>
            </section>
        @empty
            <section class="public-menu-empty">
                <span class="ui-kicker">منو</span>
                <h2>هنوز آیتمی برای نمایش نداریم.</h2>
                <p>آیتم‌ها را از مدیریت منو وارد کن تا این صفحه به‌صورت مستقیم از دیتابیس نمایش داده شود.</p>
            </section>
        @endforelse
    </div>
</main>
@endsection
