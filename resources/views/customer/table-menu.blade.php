@extends('layouts.customer')

@php
    $coverUrl = $restaurant->cover_image_path
        ? \Illuminate\Support\Facades\Storage::disk('public')->url($restaurant->cover_image_path)
        : null;

    $logoUrl = $restaurant->logo_path
        ? \Illuminate\Support\Facades\Storage::disk('public')->url($restaurant->logo_path)
        : null;

    $menuData = $menu->map(function ($category) {
        return [
            'id' => $category->id,
            'name' => $category->name,
            'items' => $category->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'categoryId' => $item->menu_category_id,
                    'name' => $item->name,
                    'description' => $item->description,
                    'price' => (int) $item->price,
                    'image' => $item->image_path
                        ? \Illuminate\Support\Facades\Storage::disk('public')->url($item->image_path)
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
@endphp

@section('title', $restaurant->name . ' — سفارش میز ' . $table->number)
@section('description', 'منوی دیجیتال و سفارش میز ' . $table->number)

@section('content')
<div id="customer-menu"
     data-table-token="{{ $qr->token }}"
     data-order-url="{{ route('table.orders.store', ['token' => $qr->token]) }}"
     data-currency="{{ $restaurant->currency }}"
     class="table-menu-page">

    <header class="customer-header">
        <div class="ui-shell customer-header__inner">
            <a class="customer-brand" href="{{ route('home') }}">
                <span class="customer-brand__mark">
                    @if($logoUrl)
                        <img src="{{ $logoUrl }}" alt="" width="42" height="42">
                    @else
                        <span>{{ mb_substr($restaurant->name, 0, 1) }}</span>
                    @endif
                </span>
                <span><strong>{{ $restaurant->name }}</strong><small>سفارش مستقیم از میز</small></span>
            </a>
            <span class="inline-flex items-center rounded-full border border-white/20 bg-white/10 px-3 py-2 text-[10px] font-bold text-white">
                میز {{ $table->number }}
            </span>
        </div>
    </header>

    <section class="table-hero">
        @if($coverUrl)
            <div class="table-hero__media"><img src="{{ $coverUrl }}" alt="" width="1600" height="1000" loading="eager"></div>
        @endif
        <div class="table-hero__veil"></div>
        <div class="table-hero__content">
            <span class="ui-kicker" style="color:var(--c-peach)">منوی میز {{ $table->number }}</span>
            <h1>چیزی که دلت می‌خواهد،<br>همین حالاست.</h1>
            <p>{{ $restaurant->description ?: 'غذا و نوشیدنی را انتخاب کن؛ انتخاب‌ها و قیمت‌ها از منوی واقعی همین رستوران می‌آیند.' }}</p>
            <label class="table-search">
                <input id="menu-search" type="search" autocomplete="off" placeholder="جست‌وجو در غذا و نوشیدنی…">
            </label>
        </div>
    </section>

    <nav class="table-nav" aria-label="دسته‌های منو">
        <div class="ui-shell table-nav__inner">
            @foreach($menu as $index => $category)
                <button type="button"
                        data-category="{{ $category->id }}"
                        class="{{ $index === 0 ? 'is-active' : '' }}">
                    {{ $category->name }}
                </button>
            @endforeach
        </div>
    </nav>

    <main class="ui-shell table-menu-content">
        <div id="empty-search" class="public-menu-empty hidden">
            <span class="ui-kicker">جست‌وجو</span>
            <h2>چیزی پیدا نشد.</h2>
            <p>نام غذا یا نوشیدنی دیگری را امتحان کن.</p>
        </div>

        @foreach($menu as $category)
            <section data-category-section="{{ $category->id }}" class="table-category">
                <div class="table-category__head">
                    <div>
                        <span class="ui-kicker">منوی واقعی</span>
                        <h2>{{ $category->name }}</h2>
                    </div>
                    <span>{{ $category->items->count() }} آیتم</span>
                </div>

                <div class="table-product-grid">
                    @forelse($category->items as $item)
                        <div data-menu-card
                             data-search="{{ mb_strtolower($item->name . ' ' . ($item->description ?? ''), 'UTF-8') }}">
                            <x-menu-item-card
                                :item="$item"
                                :restaurant="$restaurant"
                                :category-name="$category->name"
                                interactive
                            />
                        </div>
                    @empty
                        <p class="public-menu-empty">این دسته فعلاً آیتم فعالی ندارد.</p>
                    @endforelse
                </div>
            </section>
        @endforeach
    </main>

    <div class="table-cart-bar">
        <button type="button" data-open-cart class="table-cart-bar__button">
            <span class="table-cart-bar__summary">
                <span class="table-cart-bar__icon">+</span>
                <span>
                    <strong>سبد سفارش</strong>
                    <small><span data-cart-count>۰</span> آیتم</small>
                </span>
            </span>
            <strong><span data-cart-total>۰</span> {{ $restaurant->currency }}</strong>
        </button>
    </div>

    <div id="item-sheet" class="sheet" aria-hidden="true">
        <div class="sheet__backdrop" data-close-sheet></div>
        <section class="sheet__panel" role="dialog" aria-modal="true" aria-labelledby="sheet-title">
            <div class="sheet__head">
                <span>سفارشی‌سازی</span>
                <button type="button" class="sheet__close" data-close-sheet aria-label="بستن">×</button>
            </div>
            <div id="sheet-content" class="sheet__body"></div>
        </section>
    </div>

    <div id="cart-sheet" class="sheet" aria-hidden="true">
        <div class="sheet__backdrop" data-close-cart></div>
        <section class="sheet__panel" role="dialog" aria-modal="true" aria-labelledby="cart-title">
            <div class="sheet__head">
                <div><span>مرور نهایی</span><strong id="cart-title">سبد سفارش</strong></div>
                <button type="button" class="sheet__close" data-close-cart aria-label="بستن">×</button>
            </div>
            <div class="sheet__body">
                <div id="cart-items" class="cart-list"></div>
                <div id="cart-empty" class="public-menu-empty hidden">
                    <h2>سبدت خالیه.</h2>
                    <p>از منوی بالا یک مورد انتخاب کن.</p>
                </div>
                <textarea id="customer-note" class="cart-note" maxlength="500" placeholder="یادداشت برای آشپزخانه…"></textarea>
                <div id="order-alert" class="sheet-error hidden"></div>
                <button type="button" data-submit-order class="cart-submit" disabled>
                    <span data-submit-label>ثبت سفارش</span>
                    <span data-submit-spinner class="hidden">…</span>
                </button>
            </div>
        </section>
    </div>

    <div id="success-sheet" class="sheet" aria-hidden="true">
        <div class="sheet__backdrop"></div>
        <section class="sheet__panel" role="dialog" aria-modal="true">
            <div class="sheet__body text-center">
                <div class="mx-auto grid size-16 place-items-center rounded-2xl bg-[#eef4f1] text-2xl font-black text-[#3d5d5c]">✓</div>
                <p class="mt-5 text-xs text-[var(--muted)]">سفارش با موفقیت ثبت شد</p>
                <h2 class="mt-2 text-2xl font-black">سفارش <span id="success-order-number">—</span></h2>
                <p class="mt-2 text-xs leading-6 text-[var(--muted)]">سفارش برای میز {{ $table->number }} ارسال شد.</p>
                <div class="sheet-price"><span>مبلغ</span><strong><span id="success-total">۰</span> {{ $restaurant->currency }}</strong></div>
                <button type="button" data-close-success class="cart-submit">بازگشت به منو</button>
            </div>
        </section>
    </div>

    <div id="toast" class="pointer-events-none fixed inset-x-0 bottom-24 z-[120] flex justify-center px-4 opacity-0 transition duration-300">
        <div class="rounded-full bg-[var(--c-ink)] px-4 py-3 text-xs font-bold text-white shadow-xl" data-toast-message></div>
    </div>

    <script>window.GilasMenu = { data: {{ \Illuminate\Support\Js::from($menuData) }} };</script>
</div>
@endsection
