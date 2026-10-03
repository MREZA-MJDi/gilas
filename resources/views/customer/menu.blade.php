@extends('layouts.customer')

@section('title', $restaurant->name . ' — منو')
@section('description', 'منوی آنلاین ' . $restaurant->name)

@section('content')
<div class="public-menu-shell">
    <header class="public-menu-header">
        <div class="ui-shell public-menu-header__inner">
            <a href="{{ route('home') }}" class="brand-lockup" aria-label="بازگشت به خانه گیلاسی">
                <span class="brand-lockup__mark">
                    @if($restaurant->logo_path)
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($restaurant->logo_path) }}" alt="" width="44" height="44">
                    @else
                        <span aria-hidden="true">گ</span>
                    @endif
                </span>
                <span>
                    <strong>{{ $restaurant->name }}</strong>
                    <small>منوی خانه</small>
                </span>
            </a>
            <a href="{{ route('home') }}" class="public-menu-header__back">خانه</a>
        </div>
    </header>

    <main class="ui-shell public-menu">
        <section class="public-menu__hero">
            <div>
                <span class="eyebrow">منوی واقعی</span>
                <h1>انتخابت را آرام،<br><strong>خوش‌طعم</strong> شروع کن.</h1>
                <p>{{ $restaurant->description ?: 'هر چیزی که امروز در خانه گیلاسی سرو می‌شود، همین‌جا با قیمت و موجودی واقعی دیده می‌شود.' }}</p>
            </div>
            @if($restaurant->cover_image_path)
                <div class="public-menu__hero-media">
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($restaurant->cover_image_path) }}" alt="" width="960" height="640">
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
                            <span class="eyebrow">دسته</span>
                            <h2>{{ $category->name }}</h2>
                        </div>
                        <span>{{ $category->items->count() }} انتخاب</span>
                    </div>

                    <div class="public-product-grid">
                        @foreach($category->items as $item)
                            <article class="public-product-card">
                                <a href="{{ route('menu.item', ['slug' => $item->slug]) }}" class="public-product-card__media">
                                    @if($item->image_path)
                                        <img src="{{ \Illuminate\Support\Facades\Storage::url($item->image_path) }}"
                                             alt="{{ $item->name }}"
                                             loading="lazy"
                                             decoding="async"
                                             width="800"
                                             height="800">
                                    @else
                                        <span class="public-product-card__fallback" aria-hidden="true">گ</span>
                                    @endif
                                </a>
                                <div class="public-product-card__body">
                                    <div>
                                        <span class="public-product-card__category">{{ $category->name }}</span>
                                        <h3><a href="{{ route('menu.item', ['slug' => $item->slug]) }}">{{ $item->name }}</a></h3>
                                        @if($item->description)
                                            <p>{{ $item->description }}</p>
                                        @endif
                                    </div>
                                    <div class="public-product-card__footer">
                                        <strong>{{ number_format((int) $item->price) }} {{ $restaurant->currency }}</strong>
                                        <a href="{{ route('menu.item', ['slug' => $item->slug]) }}" aria-label="جزئیات {{ $item->name }}">جزئیات</a>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @empty
                <div class="public-menu-empty">
                    <span class="eyebrow">منو</span>
                    <h2>فعلاً چیزی برای نمایش نداریم.</h2>
                    <p>اطلاعات منو را از داشبورد وارد کن تا همین صفحه خودش به‌روزرسانی شود.</p>
                </div>
            @endforelse
        </div>
    </main>
</div>
@endsection
