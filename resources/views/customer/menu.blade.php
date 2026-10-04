@extends('layouts.customer')

@section('title', $restaurant->name . ' — منو')
@section('description', 'منوی آنلاین ' . $restaurant->name)
@section('body_class', 'public-menu-page')

@section('content')
<div id="public-menu" class="public-menu-shell">
    <header class="public-menu-header">
        <div class="ui-shell public-menu-header__inner">
            <a href="{{ route('home') }}" class="brand-lockup" aria-label="بازگشت به گیلاس">
                <span class="brand-lockup__mark">
                    @if($restaurant->logo_path)
                        <img src="{{ IlluminateSupportFacadesStorage::url($restaurant->logo_path) }}" alt="" width="44" height="44" decoding="async">
                    @else
                        <span aria-hidden="true">گ</span>
                    @endif
                </span>
                <span class="brand-lockup__text">
                    <strong>{{ $restaurant->name }}</strong>
                    <small>منوی دیجیتال</small>
                </span>
            </a>

            <a href="{{ route('public.reservation') }}" class="public-menu-header__action">رزرو میز</a>
        </div>
    </header>

    <main class="ui-shell public-menu">
        <section class="public-menu__hero">
            <div class="public-menu__hero-copy">
                <span class="eyebrow">منوی گیلاس</span>
                <h1>هر چیزی که<br><strong>دلت می‌خواد.</strong></h1>
                <p>{{ $restaurant->description ?: 'منو، قیمت و موجودی واقعی را همین‌جا ببین و انتخابت را شروع کن.' }}</p>

                <label class="public-menu__search">
                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none">
                        <circle cx="11" cy="11" r="6.5" stroke="currentColor" stroke-width="1.7"/>
                        <path d="m16 16 4 4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                    </svg>
                    <input
                        type="search"
                        inputmode="search"
                        autocomplete="off"
                        spellcheck="false"
                        placeholder="دنبال چی می‌گردی؟"
                        aria-label="جست‌وجو در منو"
                        data-public-search>
                </label>
                <p class="public-menu__search-status" data-search-status aria-live="polite"></p>
            </div>

            @if($restaurant->cover_image_path)
                <div class="public-menu__hero-media">
                    <img src="{{ IlluminateSupportFacadesStorage::url($restaurant->cover_image_path) }}"
                         alt="{{ $restaurant->name }}"
                         width="960"
                         height="720"
                         loading="eager"
                         decoding="async">
                    <span aria-hidden="true">گیلاس</span>
                </div>
            @endif
        </section>

        @if($featuredItems->isNotEmpty())
            <section class="public-menu__featured" aria-labelledby="featured-heading">
                <div class="public-menu__section-head">
                    <div>
                        <span class="eyebrow">انتخاب‌های سریع</span>
                        <h2 id="featured-heading">پیشنهاد امروز</h2>
                    </div>
                    <span class="public-menu__section-note">از منوی واقعی</span>
                </div>

                <div class="ui-scroll-x public-menu__featured-grid">
                    @foreach($featuredItems as $entry)
                        @php($featuredItem = $entry['item'])
                        @php($featuredCategory = $entry['category'])
                        <a href="{{ route('menu.item', ['slug' => $featuredItem->slug]) }}" class="public-featured-card">
                            <div class="public-featured-card__media">
                                @if($featuredItem->image_path)
                                    <img src="{{ IlluminateSupportFacadesStorage::url($featuredItem->image_path) }}"
                                         alt="{{ $featuredItem->name }}"
                                         width="640"
                                         height="640"
                                         loading="lazy"
                                         decoding="async">
                                @else
                                    <span aria-hidden="true">گ</span>
                                @endif
                            </div>
                            <div class="public-featured-card__body">
                                <span>{{ $featuredCategory->name }}</span>
                                <strong>{{ $featuredItem->name }}</strong>
                                <b>{{ number_format((int) $featuredItem->price) }} {{ $restaurant->currency }}</b>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        <nav class="public-menu__filters" aria-label="دسته‌های منو">
            <a href="{{ route('menu.index') }}"
               class="{{ blank($selectedSlug) ? 'is-active' : '' }}"
               data-public-category="all">
                همه
            </a>
            @foreach($menu as $category)
                <a href="{{ route('menu.category', ['slug' => $category->slug]) }}"
                   class="{{ $selectedSlug === $category->slug ? 'is-active' : '' }}"
                   data-public-category="{{ $category->slug }}">
                    {{ $category->name }}
                </a>
            @endforeach
        </nav>

        <div class="public-menu__sections">
            @forelse($menu as $category)
                <section class="public-menu-category"
                         id="category-{{ $category->slug }}"
                         data-public-section="{{ $category->slug }}">
                    <div class="public-menu-category__heading">
                        <div>
                            <span class="eyebrow">دسته</span>
                            <h2>{{ $category->name }}</h2>
                        </div>
                        <span data-category-count>{{ $category->items->count() }} انتخاب</span>
                    </div>

                    <div class="public-product-grid">
                        @foreach($category->items as $item)
                            <article class="public-product-card"
                                     data-public-card
                                     data-search="{{ mb_strtolower(trim($item->name . ' ' . ($item->description ?? '')), 'UTF-8') }}">
                                <a href="{{ route('menu.item', ['slug' => $item->slug]) }}" class="public-product-card__media">
                                    @if($item->image_path)
                                        <img src="{{ IlluminateSupportFacadesStorage::url($item->image_path) }}"
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
                                    <div class="public-product-card__main">
                                        <span class="public-product-card__category">{{ $category->name }}</span>
                                        <h3><a href="{{ route('menu.item', ['slug' => $item->slug]) }}">{{ $item->name }}</a></h3>
                                        @if($item->description)
                                            <p>{{ $item->description }}</p>
                                        @endif
                                    </div>

                                    <div class="public-product-card__footer">
                                        <strong>{{ number_format((int) $item->price) }} {{ $restaurant->currency }}</strong>
                                        <span class="public-product-card__open">دیدن جزئیات <b aria-hidden="true">↗</b></span>
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
                    <p>آیتم‌های فعال و موجود را از داشبورد وارد کن تا این صفحه به‌صورت خودکار به‌روز شود.</p>
                </div>
            @endforelse
        </div>

        <div class="public-menu__search-empty" data-search-empty hidden>
            <span class="eyebrow">جست‌وجو</span>
            <h2>این یکی را پیدا نکردیم.</h2>
            <p>اسم، توضیح یا دسته‌ی دیگری را امتحان کن.</p>
            <button type="button" data-clear-search>پاک کردن جست‌وجو</button>
        </div>
    </main>
</div>
@endsection
