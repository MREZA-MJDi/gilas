@extends('layouts.customer')

@section('body_class', 'customer-table-menu')
@section('hide_footer', '1')

@php
    $coverUrl = $restaurant->cover_image_path
        ? \Illuminate\Support\Facades\Storage::url($restaurant->cover_image_path)
        : null;
    $logoUrl = $restaurant->logo_path
        ? \Illuminate\Support\Facades\Storage::url($restaurant->logo_path)
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
                    'image' => $item->image_url,
                    'variants' => $item->variants->map(fn ($variant) => [
                        'id' => $variant->id, 'name' => $variant->name, 'price' => (int) $variant->price,
                    ])->values()->all(),
                    'options' => $item->options->map(function ($option) {
                        return [
                            'id' => $option->id,
                            'name' => $option->name,
                            'required' => (bool) $option->is_required,
                            'min' => (int) $option->min_select,
                            'max' => (int) $option->max_select,
                            'values' => $option->values->map(fn ($value) => [
                                'id' => $value->id, 'name' => $value->name, 'priceDelta' => (int) $value->price_delta,
                            ])->values()->all(),
                        ];
                    })->values()->all(),
                ];
            })->values()->all(),
        ];
    })->values()->all();
@endphp

@section('title', 'گیلاس — منوی دیجیتال')
@section('description', 'منوی دیجیتال ' . $restaurant->name . ' برای میز ' . $table->number)

@section('content')
<div id="customer-menu"
     data-table-token="{{ $qr->token }}"
     data-order-url="{{ route('table.orders.store', $qr->token) }}"
     data-currency="{{ $restaurant->currency }}"
     class="min-h-screen pb-28">

    <header class="sticky top-0 z-40 border-b border-white/10 bg-[#120f0d]/85 backdrop-blur-2xl">
        <div class="mx-auto flex h-16 max-w-6xl items-center justify-between gap-3 px-4 sm:px-6">
            <div class="flex min-w-0 items-center gap-3">
                <div class="grid size-10 shrink-0 place-items-center overflow-hidden rounded-2xl border border-white/10 bg-white/8">
                    @if($logoUrl)
                        <img src="{{ $logoUrl }}" alt="{{ $restaurant->name }}" class="size-full object-cover" width="40" height="40" decoding="async">
                    @else
                        <span class="text-lg font-bold text-rose-100">گ</span>
                    @endif
                </div>
                <div class="min-w-0">
                    <p class="truncate text-sm font-semibold text-white">{{ $restaurant->name }}</p>
                    <p class="truncate text-xs text-stone-500">منوی دیجیتال</p>
                </div>
            </div>
            <span class="inline-flex shrink-0 items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3 py-1.5 text-xs text-stone-200">
                <span class="size-1.5 rounded-full bg-emerald-300 shadow-[0_0_12px_rgba(110,231,183,.8)]"></span>
                میز {{ $table->number }}
            </span>
        </div>
    </header>

    <main>
        <section class="mx-auto max-w-6xl px-4 pt-4 sm:px-6 sm:pt-7">
            <div class="relative isolate overflow-hidden rounded-[2rem] border border-white/10 bg-[#211916] shadow-2xl shadow-black/20">
                @if($coverUrl)
                    <img src="{{ $coverUrl }}" alt="" class="absolute inset-0 size-full object-cover opacity-45" loading="eager">
                @endif
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_18%_0%,rgba(244,114,182,.22),transparent_38%),linear-gradient(120deg,rgba(18,15,13,.54),rgba(18,15,13,.94))]"></div>
                <div class="relative flex min-h-[270px] flex-col justify-end p-6 sm:min-h-[320px] sm:p-9">
                    <span class="mb-4 inline-flex w-fit items-center rounded-full border border-white/10 bg-black/20 px-3 py-1.5 text-xs font-medium text-stone-200 backdrop-blur">
                        سفارش مستقیم از میز {{ $table->number }}
                    </span>
                    <h1 class="text-3xl font-bold tracking-tight text-white sm:text-5xl">{{ $restaurant->name }}</h1>
                    <p class="mt-3 max-w-2xl text-sm leading-7 text-stone-300 sm:text-base">منو را ببین، غذایت را شخصی‌سازی کن و سفارش را مستقیم برای آشپزخانه بفرست.</p>
                    <label class="relative mt-6 block max-w-xl">
                        <svg class="pointer-events-none absolute right-4 top-1/2 size-5 -translate-y-1/2 text-stone-400" viewBox="0 0 24 24" fill="none"><path d="m21 21-4.35-4.35m1.35-5.15a6.5 6.5 0 1 1-13 0 6.5 6.5 0 0 1 13 0Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                        <input id="menu-search" type="search" autocomplete="off" placeholder="دنبال چه چیزی می‌گردی؟"
                               class="h-14 w-full rounded-2xl border border-white/10 bg-black/25 pr-12 pl-4 text-sm text-white outline-none placeholder:text-stone-500 transition focus:border-rose-200/40">
                    </label>
                </div>
            </div>
        </section>

        <nav class="sticky top-16 z-30 mt-5 border-y border-white/8 bg-[#120f0d]/88 backdrop-blur-xl">
            <div class="mx-auto max-w-6xl overflow-x-auto px-4 sm:px-6 scrollbar-none">
                <div class="flex min-w-max gap-2 py-3" role="tablist">
                    @foreach($menu as $index => $category)
                        <button type="button" data-category="{{ $category->id }}"
                                class="category-pill rounded-full border px-4 py-2 text-sm font-medium transition {{ $index === 0 ? 'border-rose-200/25 bg-rose-100 text-stone-950' : 'border-white/10 bg-white/5 text-stone-300 hover:bg-white/10' }}">
                            {{ $category->name }}
                        </button>
                    @endforeach
                </div>
            </div>
        </nav>

        <section class="mx-auto max-w-6xl px-4 py-8 sm:px-6 sm:py-10">
            <div id="empty-search" class="hidden rounded-3xl border border-dashed border-white/10 bg-white/[0.025] px-6 py-16 text-center">
                <div class="mx-auto grid size-14 place-items-center rounded-2xl bg-white/5 text-stone-400">
                    <svg viewBox="0 0 24 24" class="size-6" fill="none"><circle cx="11" cy="11" r="6.5" stroke="currentColor" stroke-width="1.7"/><path d="m16 16 4 4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                </div>
                <h2 class="mt-4 text-lg font-semibold text-white">چیزی پیدا نشد</h2>
                <p class="mt-2 text-sm text-stone-500">با نام یا توضیحات دیگر جست‌وجو کن.</p>
            </div>

            <div class="space-y-12">
                @foreach($menu as $category)
                    <section data-category-section="{{ $category->id }}" class="scroll-mt-36">
                        <div class="mb-5 flex items-end justify-between gap-4">
                            <div>
                                <p class="text-[11px] font-medium uppercase tracking-[0.18em] text-rose-200/65">Category</p>
                                <h2 class="mt-1 text-2xl font-bold text-white sm:text-3xl">{{ $category->name }}</h2>
                            </div>
                            <span class="text-xs text-stone-500">{{ $category->items->count() }} آیتم</span>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            @forelse($category->items as $item)
                                <article data-menu-card data-item-id="{{ $item->id }}"
                                         data-search="{{ mb_strtolower($item->name . ' ' . ($item->description ?? ''), 'UTF-8') }}"
                                         class="group overflow-hidden rounded-3xl border border-white/8 bg-white/[0.035] transition duration-300 hover:-translate-y-0.5 hover:border-white/15 hover:bg-white/[0.06]">
                                    <button type="button" data-open-item="{{ $item->id }}" class="block w-full text-right">
                                        <div class="relative aspect-[16/10] overflow-hidden bg-[#27201c]">
                                            @if($item->image_path)
                                                <img src="{{ \Illuminate\Support\Facades\Storage::url($item->image_path) }}" alt="{{ $item->name }}" loading="lazy" decoding="async" width="800" height="800"
                                                     class="size-full object-contain p-[7%] transition duration-700 group-hover:scale-[1.035]">
                                            @else
                                                <div class="grid size-full place-items-center bg-[radial-gradient(circle_at_30%_20%,rgba(244,114,182,.16),transparent_36%),linear-gradient(135deg,#2b211d,#171311)]"><span class="text-5xl font-bold text-white/10">گ</span></div>
                                            @endif
                                            <div class="absolute inset-x-0 bottom-0 h-24 bg-gradient-to-t from-[#171311] to-transparent"></div>
                                            @if($item->variants->isNotEmpty() || $item->options->isNotEmpty())
                                                <span class="absolute right-3 top-3 rounded-full border border-white/10 bg-black/40 px-2.5 py-1 text-[11px] font-medium text-white backdrop-blur">قابل شخصی‌سازی</span>
                                            @endif
                                        </div>
                                        <div class="p-4">
                                            <div class="flex items-start justify-between gap-4">
                                                <div class="min-w-0">
                                                    <h3 class="truncate text-base font-semibold text-white">{{ $item->name }}</h3>
                                                    @if($item->description)
                                                        <p class="mt-1 line-clamp-2 text-sm leading-6 text-stone-400">{{ $item->description }}</p>
                                                    @endif
                                                    @if($item->options->isNotEmpty())
                                                        <span class="sr-only" aria-label="گزینه‌های قابل انتخاب">
                                                            @foreach($item->options as $option)
                                                                @foreach($option->values as $value)
                                                                    {{ $value->name }}
                                                                @endforeach
                                                            @endforeach
                                                        </span>
                                                    @endif
                                                </div>
                                                <span class="shrink-0 rounded-xl bg-white/5 px-2.5 py-2 text-sm font-semibold text-rose-100">{{ number_format((int) $item->price) }}</span>
                                            </div>
                                            <div class="mt-4 flex items-center justify-between">
                                                <span class="text-xs text-stone-500">{{ $item->variants->isNotEmpty() ? $item->variants->count() . ' سایز' : ($item->options->isNotEmpty() ? 'انتخاب‌های بیشتر' : 'سفارش مستقیم') }}</span>
                                                <span class="inline-flex size-9 items-center justify-center rounded-xl bg-white/8 text-stone-200 transition group-hover:bg-rose-100 group-hover:text-stone-950">
                                                    <svg viewBox="0 0 24 24" class="size-5" fill="none"><path d="M12 5v14m-7-7h14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                                                </span>
                                            </div>
                                        </div>
                                    </button>
                                </article>
                            @empty
                                <p class="col-span-full rounded-3xl border border-dashed border-white/10 py-12 text-center text-sm text-stone-500">در این دسته آیتم فعالی وجود ندارد.</p>
                            @endforelse
                        </div>
                    </section>
                @endforeach
            </div>
        </section>
    </main>

    <div class="pointer-events-none fixed inset-x-0 bottom-0 z-50 px-3 pb-[max(12px,env(safe-area-inset-bottom))] sm:px-6">
        <div data-cart-shell class="mx-auto max-w-3xl translate-y-4 opacity-0 transition duration-300">
            <button type="button" data-open-cart class="pointer-events-auto flex w-full items-center justify-between gap-4 rounded-2xl border border-white/10 bg-[#231b18]/95 px-4 py-3 shadow-2xl shadow-black/40 backdrop-blur-2xl sm:px-5">
                <span class="flex items-center gap-3">
                    <span class="grid size-10 place-items-center rounded-xl bg-rose-100 text-stone-950">🛒</span>
                    <span class="text-right"><span class="block text-sm font-semibold text-white">سبد سفارش</span><span class="block text-xs text-stone-400"><span data-cart-count>۰</span> آیتم</span></span>
                </span>
                <span class="text-sm font-bold text-rose-100"><span data-cart-total>۰</span> {{ $restaurant->currency }}</span>
            </button>
        </div>
    </div>

    <div id="item-sheet" class="fixed inset-0 z-[70] hidden items-end justify-center p-0 sm:items-center sm:p-6" aria-hidden="true">
        <div data-close-sheet class="absolute inset-0 bg-black/70 backdrop-blur-sm"></div>
        <section class="relative max-h-[92svh] w-full overflow-y-auto rounded-t-[2rem] border border-white/10 bg-[#1b1512] shadow-2xl sm:max-w-2xl sm:rounded-[2rem]" role="dialog" aria-modal="true" aria-labelledby="sheet-title">
            <div class="sticky top-0 z-10 flex items-center justify-between border-b border-white/8 bg-[#1b1512]/92 px-5 py-4 backdrop-blur-xl">
                <span class="text-xs text-stone-500">سفارشی‌سازی</span>
                <button type="button" data-close-sheet class="grid size-10 place-items-center rounded-xl bg-white/5 text-stone-300" aria-label="بستن">×</button>
            </div>
            <div id="sheet-content" class="p-5 sm:p-7"></div>
        </section>
    </div>

    <div id="cart-sheet" class="fixed inset-0 z-[75] hidden items-end justify-center p-0 sm:items-center sm:p-6" aria-hidden="true">
        <div data-close-cart class="absolute inset-0 bg-black/70 backdrop-blur-sm"></div>
        <section class="relative max-h-[92svh] w-full overflow-y-auto rounded-t-[2rem] border border-white/10 bg-[#1b1512] shadow-2xl sm:max-w-2xl sm:rounded-[2rem]" role="dialog" aria-modal="true" aria-labelledby="cart-title">
            <div class="sticky top-0 z-10 flex items-center justify-between border-b border-white/8 bg-[#1b1512]/92 px-5 py-4 backdrop-blur-xl">
                <div><p class="text-xs text-stone-500">مرور نهایی</p><h2 id="cart-title" class="mt-0.5 text-lg font-bold text-white">سبد سفارش</h2></div>
                <button type="button" data-close-cart class="grid size-10 place-items-center rounded-xl bg-white/5 text-stone-300" aria-label="بستن">×</button>
            </div>
            <div class="p-5 sm:p-7">
                <div id="cart-items" class="space-y-3"></div>
                <div id="cart-empty" class="hidden rounded-3xl border border-dashed border-white/10 bg-white/[0.025] px-5 py-12 text-center">
                    <p class="text-base font-semibold text-white">سبدت خالیه</p>
                    <p class="mt-1 text-sm text-stone-500">از منو یک غذا انتخاب کن.</p>
                </div>
                <section class="mt-5 rounded-3xl border border-white/8 bg-white/[0.035] p-4" aria-labelledby="table-payment-heading">
                    <p id="table-payment-heading" class="text-sm font-medium text-stone-200">چطور پرداخت می‌کنی؟</p>
                    <div class="mt-3 grid gap-2 sm:grid-cols-2">
                        <label class="table-payment-choice is-selected">
                            <input type="radio" name="payment-method" value="online" checked>
                            <span>
                                <strong>پرداخت آنلاین</strong>
                                <small>پرداخت امن قبل از تسویه سفارش</small>
                            </span>
                        </label>
                        <label class="table-payment-choice">
                            <input type="radio" name="payment-method" value="cashier">
                            <span>
                                <strong>پرداخت در صندوق</strong>
                                <small>تسویه هنگام خروج از کافه</small>
                            </span>
                        </label>
                    </div>
                </section>

                <div class="mt-5 rounded-3xl border border-white/8 bg-white/[0.035] p-4">
                    <label for="customer-note" class="text-sm font-medium text-stone-200">یادداشت برای آشپزخانه</label>
                    <textarea id="customer-note" rows="3" maxlength="500" placeholder="مثلاً بدون پیاز یا نکته‌ای که لازم است بدانیم..."
                              class="mt-3 w-full resize-none rounded-2xl border border-white/10 bg-black/10 px-4 py-3 text-sm text-white outline-none placeholder:text-stone-600 focus:border-rose-200/35"></textarea>
                </div>
                <div id="order-alert" class="mt-4 hidden rounded-2xl border border-red-300/15 bg-red-400/5 px-4 py-3 text-sm leading-6 text-red-200"></div>
                <button type="button" data-submit-order disabled class="mt-5 flex h-14 w-full items-center justify-center gap-2 rounded-2xl bg-rose-100 px-5 text-sm font-bold text-stone-950 disabled:cursor-not-allowed disabled:opacity-50">
                    <span data-submit-label>ثبت سفارش</span><span data-submit-spinner class="hidden size-4 animate-spin rounded-full border-2 border-stone-950/25 border-t-stone-950"></span>
                </button>
            </div>
        </section>
    </div>

    <div id="success-sheet" class="fixed inset-0 z-[90] hidden items-center justify-center bg-[#120f0d] px-5" aria-hidden="true">
        <div class="w-full max-w-md text-center">
            <div class="mx-auto grid size-20 place-items-center rounded-[1.75rem] bg-emerald-300/10 text-emerald-200 ring-1 ring-emerald-200/15">✓</div>
            <p class="mt-6 text-sm text-stone-500">سفارش با موفقیت ثبت شد</p>
            <h2 class="mt-1 text-3xl font-bold text-white">سفارش <span id="success-order-number"></span></h2>
            <p class="mt-4 text-sm leading-7 text-stone-400">سفارش شما برای میز {{ $table->number }} ارسال شد.</p>
            <div class="mt-8 rounded-3xl border border-white/8 bg-white/[0.035] p-4">
                <div class="flex items-center justify-between gap-4 text-sm"><span class="text-stone-500">مبلغ نهایی</span><strong class="text-rose-100"><span id="success-total">۰</span> {{ $restaurant->currency }}</strong></div>
            </div>
            <a id="success-track-link"
               href="#"
               class="mt-4 flex h-13 w-full items-center justify-center rounded-2xl bg-rose-100 text-sm font-bold text-stone-950">
                پیگیری سفارش
            </a>
            <button type="button" data-close-success class="mt-3 h-13 w-full rounded-2xl border border-white/10 bg-white/5 text-sm font-semibold text-white">بازگشت به منو</button>
        </div>
    </div>

    <div id="toast" class="pointer-events-none fixed inset-x-0 bottom-28 z-[100] flex justify-center px-4 opacity-0 transition duration-300">
        <div class="rounded-full border border-white/10 bg-[#241c18]/95 px-4 py-2.5 text-sm text-stone-100 shadow-xl shadow-black/30" data-toast-message></div>
    </div>

    <script>window.GilasMenu = { data: {{ \Illuminate\Support\Js::from($menuData) }} };</script>
</div>
@endsection
