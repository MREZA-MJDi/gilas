@extends('layouts.customer')

@section('title', $restaurant->name . ' — منو')
@section('description', 'منوی دیجیتال ' . $restaurant->name)
@section('body_class', 'public-menu-page')

@php
    $menuData = $menu->map(function ($category) {
        return [
            'id' => $category->id,
            'slug' => $category->slug,
            'name' => $category->name,
            'items' => $category->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'slug' => $item->slug,
                    'name' => $item->name,
                    'description' => $item->description,
                    'price' => (int) $item->price,
                    'image' => $item->image_path
                        ? \Illuminate\Support\Facades\Storage::url($item->image_path)
                        : null,
                    'variants' => $item->variants->map(fn ($variant) => [
                        'id' => $variant->id,
                        'name' => $variant->name,
                        'price' => (int) $variant->price,
                    ])->values()->all(),
                    'options' => $item->options->map(function ($option) {
                        return [
                            'id' => $option->id,
                            'name' => $option->name,
                            'required' => (bool) $option->is_required,
                            'min' => (int) $option->min_select,
                            'max' => (int) $option->max_select,
                            'values' => $option->values->map(fn ($value) => [
                                'id' => $value->id,
                                'name' => $value->name,
                                'priceDelta' => (int) $value->price_delta,
                            ])->values()->all(),
                        ];
                    })->values()->all(),
                ];
            })->values()->all(),
        ];
    })->values()->all();

    $requestedCategory = filled($selectedSlug)
        ? $menu->firstWhere('slug', $selectedSlug)
        : null;

    $initialCategory = $requestedCategory?->items->isNotEmpty()
        ? $requestedCategory
        : $menu->first(fn ($category) => $category->items->isNotEmpty());

    $initialItem = $initialCategory?->items->first();

    $tableToken = request()->cookie('gilas_table_token');
    $orderUrl = $tableToken
        ? route('table.orders.store', $tableToken)
        : '';
@endphp

@section('content')
<div id="public-menu"
     class="public-menu-shell"
     data-order-url="{{ $orderUrl }}"
     data-table-token="{{ $tableToken ?: '' }}"
     data-currency="{{ $restaurant->currency }}"
     data-initial-category="{{ $initialCategory?->id }}"
     data-initial-item="{{ $initialItem?->id }}">

    <header class="public-menu-header">
        <div class="ui-shell public-menu-header__inner">
            <a href="{{ route('home') }}" class="brand-lockup" aria-label="بازگشت به گیلاس">
                <span class="brand-lockup__mark" aria-hidden="true">
                    <img src="{{ asset('favicon.svg') }}" alt="" width="44" height="44" decoding="async">
                </span>
                <span class="brand-lockup__text">
                    <strong>{{ $restaurant->name }}</strong>
                    <small>منوی دیجیتال</small>
                </span>
            </a>

            <div class="menu-breadcrumb" aria-live="polite">
                <span>منو</span>
                <b>/</b>
                <strong data-current-category>{{ $initialCategory?->name ?: 'انتخاب' }}</strong>
                <b>/</b>
                <span data-current-item>{{ $initialItem?->name ?: '—' }}</span>
            </div>

            <button type="button" class="public-menu-header__cart" data-open-cart aria-label="باز کردن سبد سفارش">
                <span class="public-menu-header__cart-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M3.5 4.5h2l1.5 9.1a1.8 1.8 0 0 0 1.8 1.5h8.8a1.8 1.8 0 0 0 1.7-1.3L21 7H7" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle cx="10" cy="19" r="1.4" fill="currentColor"/>
                        <circle cx="18" cy="19" r="1.4" fill="currentColor"/>
                    </svg>
                    <span data-header-cart-count>۰</span>
                </span>
                <span>سبد سفارش</span>
            </button>
        </div>
    </header>

    <main class="ui-shell public-menu">
        <section class="menu-explorer" aria-label="منوی تعاملی گیلاس">
            <div class="menu-explorer__intro">
                <div>
                    <span class="eyebrow">DIGITAL MENU</span>
                    <h1>از غذا شروع کن،<br><strong>بقیه‌اش خودش میاد.</strong></h1>
                </div>
                <p>{{ $restaurant->description ?: 'دسته را انتخاب کن، غذای موردنظرت را ببین و بدون خروج از همین صفحه انتخابش کن.' }}</p>
            </div>

            @if($initialCategory && $initialItem)
                <div class="menu-explorer__layout">
                    <aside class="menu-rail menu-rail--categories" aria-label="دسته‌های منو">
                        <div class="menu-rail__heading">
                            <span>01</span>
                            <strong>دسته‌ها</strong>
                        </div>

                        <div class="menu-category-list" data-category-list>
                            @foreach($menu as $index => $category)
                                <button type="button"
                                        class="menu-category-button {{ $category->id === $initialCategory->id ? 'is-active' : '' }}"
                                        data-category-select="{{ $category->id }}"
                                        aria-pressed="{{ $category->id === $initialCategory->id ? 'true' : 'false' }}">
                                    <span class="menu-category-button__index">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                                    <span class="menu-category-button__name">{{ $category->name }}</span>
                                    <span class="menu-category-button__arrow" aria-hidden="true">↙</span>
                                </button>
                            @endforeach
                        </div>
                    </aside>

                    <section class="menu-focus" aria-live="polite" aria-atomic="true">
                        <div class="menu-focus__halo" aria-hidden="true"></div>
                        <div class="menu-focus__grain" aria-hidden="true"></div>

                        <div class="menu-focus__topline">
                            <span data-focus-category>{{ $initialCategory?->name }}</span>
                            <span data-focus-state>موجود</span>
                        </div>

                        <div class="menu-focus__media-wrap">
                            <div class="menu-focus__shadow" aria-hidden="true"></div>
                            <div class="menu-focus__media">
                                <img data-focus-image
                                     src="{{ $initialItem->image_path ? \Illuminate\Support\Facades\Storage::url($initialItem->image_path) : '' }}"
                                     alt="{{ $initialItem?->name ?: '' }}"
                                     width="900"
                                     height="900"
                                     decoding="async"
                                     fetchpriority="high"
                                     @if(!$initialItem->image_path) hidden @endif>
                                <div data-focus-fallback class="menu-focus__fallback" @if(!$initialItem?->image_path) hidden @endif aria-hidden="true">گ</div>
                            </div>
                        </div>

                        <div class="menu-focus__details">
                            <div class="menu-focus__title-row">
                                <div>
                                    <span class="eyebrow">انتخاب فعلی</span>
                                    <h2 data-focus-name>{{ $initialItem->name }}</h2>
                                </div>
                                <strong class="menu-focus__price">
                                    <span data-focus-price>{{ number_format((int) ($initialItem?->price ?? 0)) }}</span>
                                    {{ $restaurant->currency }}
                                </strong>
                            </div>

                            <p data-focus-description>{{ $initialItem?->description ?: 'توضیحات این آیتم را از منوی اصلی دنبال کن.' }}</p>

                            <div class="menu-focus__actions">
                                <button type="button" class="menu-primary-action" data-focus-add>
                                    <span>افزودن به سفارش</span>
                                    <b>+</b>
                                </button>
                                <button type="button" class="menu-secondary-action" data-focus-open-details>
                                    جزئیات و انتخاب‌ها
                                </button>
                            </div>
                        </div>
                    </section>

                    <aside class="menu-rail menu-rail--items" aria-label="آیتم‌های دسته انتخاب‌شده">
                        <div class="menu-rail__heading">
                            <span>02</span>
                            <strong data-items-heading>{{ $initialCategory?->name }}</strong>
                        </div>

                        <div class="menu-item-list" data-item-list>
                            @foreach($initialCategory->items as $item)
                                <button type="button"
                                        class="menu-item-button {{ $item->id === $initialItem->id ? 'is-active' : '' }}"
                                        data-item-select="{{ $item->id }}"
                                        aria-pressed="{{ $item->id === $initialItem->id ? 'true' : 'false' }}">
                                    <span class="menu-item-button__thumb">
                                        @if($item->image_path)
                                            <img src="{{ \Illuminate\Support\Facades\Storage::url($item->image_path) }}" alt="" width="96" height="96" loading="lazy" decoding="async">
                                        @else
                                            <span>گ</span>
                                        @endif
                                    </span>
                                    <span class="menu-item-button__copy">
                                        <strong>{{ $item->name }}</strong>
                                        <small>{{ number_format((int) $item->price) }} {{ $restaurant->currency }}</small>
                                    </span>
                                    <span class="menu-item-button__arrow" aria-hidden="true">↘</span>
                                </button>
                            @endforeach
                        </div>
                    </aside>
                </div>

                <section class="menu-related" aria-labelledby="related-heading">
                    <div class="menu-related__heading">
                        <div>
                            <span class="eyebrow">03 / SUGGESTED</span>
                            <h2 id="related-heading">کنارش چی می‌چسبه؟</h2>
                        </div>
                        <span>از باقی منو، مرتبط با انتخاب تو</span>
                    </div>

                    <div class="ui-scroll-x menu-related__list" data-related-list></div>
                </section>

                <noscript class="menu-no-js-links">
                    <span>مرور مستقیم منو:</span>
                    @foreach($menu as $fallbackCategory)
                        <span class="menu-no-js-links__group">
                            <strong>{{ $fallbackCategory->name }}</strong>
                            @foreach($fallbackCategory->items as $fallbackItem)
                                <a href="{{ route('menu.item', ['slug' => $fallbackItem->slug]) }}">{{ $fallbackItem->name }}</a>
                            @endforeach
                        </span>
                    @endforeach
                </noscript>
            @else
                <div class="public-menu-empty">
                    <span class="eyebrow">منو</span>
                    <h2>فعلاً چیزی برای نمایش نداریم.</h2>
                    <p>آیتم‌های فعال و موجود را از داشبورد وارد کن تا این صفحه به‌صورت خودکار زنده شود.</p>
                </div>
            @endif
        </section>
    </main>
</div>

<div id="public-menu-item-sheet"
     class="menu-dialog"
     hidden
     aria-hidden="true">
    <div class="menu-dialog__backdrop" data-close-menu-dialog></div>
    <section class="menu-dialog__panel" role="dialog" aria-modal="true" aria-labelledby="menu-dialog-title">
        <div class="menu-dialog__header">
            <div>
                <span class="eyebrow">PERSONALIZE</span>
                <h2 id="menu-dialog-title">انتخاب‌ها</h2>
            </div>
            <button type="button" class="menu-dialog__close" data-close-menu-dialog aria-label="بستن">×</button>
        </div>
        <div class="menu-dialog__content" data-dialog-content></div>
    </section>
</div>

<div id="public-menu-cart"
     class="menu-dialog menu-dialog--cart"
     hidden
     aria-hidden="true">
    <div class="menu-dialog__backdrop" data-close-cart></div>
    <section class="menu-dialog__panel" role="dialog" aria-modal="true" aria-labelledby="public-cart-title">
        <div class="menu-dialog__header">
            <div>
                <span class="eyebrow">ORDER</span>
                <h2 id="public-cart-title">سبد سفارش</h2>
            </div>
            <button type="button" class="menu-dialog__close" data-close-cart aria-label="بستن">×</button>
        </div>

        <div class="menu-cart__content">
            <div data-public-cart-items class="menu-cart__items"></div>

            <div data-public-cart-empty class="menu-cart__empty">
                <span aria-hidden="true">گ</span>
                <strong>هنوز چیزی انتخاب نکردی.</strong>
                <p>از دسته‌ها یا پیشنهادهای پایین شروع کن.</p>
            </div>

            <div class="menu-cart__note">
                <label for="public-customer-note">یادداشت</label>
                <textarea id="public-customer-note" rows="3" maxlength="500" placeholder="مثلاً بدون پیاز..."></textarea>
            </div>

            <div class="menu-cart__footer">
                <div>
                    <span>مجموع</span>
                    <strong><span data-public-cart-total>۰</span> {{ $restaurant->currency }}</strong>
                </div>
                <button type="button" class="menu-primary-action" data-public-submit-order disabled>
                    <span data-public-submit-label>ثبت سفارش</span>
                    <span data-public-submit-spinner hidden>...</span>
                </button>
            </div>

            <p class="menu-cart__hint" data-public-cart-hint></p>
            <div class="menu-cart__alert" data-public-cart-alert hidden></div>
        </div>
    </section>
</div>

@unless(app()->environment('testing'))
    <script>
        window.GilasPublicMenu = @json($menuData);
    </script>
@endunless
@endsection
