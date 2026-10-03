@extends('layouts.customer')

@section('title', 'در انتظار آماده‌شدن سفارش — ' . $order->order_number)
@section('description', 'پیگیری سفارش ' . $order->order_number . ' در خانه گیلاسی')

@section('content')
<div id="order-waiting"
     data-status-url="{{ $statusUrl }}"
     data-order-number="{{ $order->order_number }}"
     data-currency="{{ $order->restaurant->currency }}"
     data-initial-status="{{ $order->status->value }}"
     class="min-h-screen px-4 pb-10 pt-4 sm:px-6">

    <main class="mx-auto flex min-h-[calc(100svh-2rem)] max-w-5xl flex-col">
        <header class="relative z-10 flex items-center justify-between gap-3 border-b border-white/8 pb-4">
            <div class="flex min-w-0 items-center gap-3">
                <div class="grid size-11 shrink-0 place-items-center rounded-2xl border border-white/10 bg-white/5 font-black text-rose-100">گ</div>
                <div class="min-w-0">
                    <p class="truncate text-sm font-semibold text-white">{{ $order->restaurant->name }}</p>
                    <p class="truncate text-xs text-stone-500">
                        سفارش {{ $order->order_number }}
                        @if($order->table)
                            · میز {{ $order->table->number }}
                        @endif
                    </p>
                </div>
            </div>

            <a href="{{ url('/table/' . request()->cookie('gilas_table_token')) }}"
               class="hidden rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-xs font-semibold text-stone-300 hover:bg-white/10 sm:inline-flex">
                بازگشت به منو
            </a>
        </header>

        <section class="relative z-10 flex flex-1 flex-col justify-center py-7 sm:py-10">
            <div class="mx-auto w-full max-w-3xl text-center">
                <div id="status-badge" class="mx-auto inline-flex items-center gap-2 rounded-full border border-amber-200/15 bg-amber-200/5 px-3.5 py-2 text-xs font-semibold text-amber-100">
                    <span class="size-1.5 rounded-full bg-amber-200"></span>
                    <span data-status-label>{{ $order->status->value === 'ready' ? 'آماده شد' : 'در حال آماده‌سازی' }}</span>
                </div>

                <h1 class="mt-5 text-3xl font-black tracking-tight text-white sm:text-5xl">
                    چند دقیقه‌ی خوشمزه در راهه
                </h1>
                <p class="mx-auto mt-3 max-w-xl text-sm leading-7 text-stone-400 sm:text-base">
                    تا وقتی آشپزخانه سفارش را آماده می‌کند، یک بازی کوتاه داریم.
                </p>

                <div class="mt-7 flex items-center justify-center gap-1.5 sm:gap-2" aria-label="وضعیت سفارش">
                    @php
                        $steps = [
                            ['pending', 'ثبت'],
                            ['confirmed', 'تأیید'],
                            ['preparing', 'آماده‌سازی'],
                            ['ready', 'آماده'],
                            ['served', 'تحویل'],
                        ];
                    @endphp
                    @foreach($steps as $step)
                        <div class="flex items-center">
                            <div data-progress-step="{{ $step[0] }}"
                                 class="grid size-8 place-items-center rounded-full border border-white/10 bg-white/5 text-[10px] font-bold text-stone-500 sm:size-9">
                                {{ $loop->iteration }}
                            </div>
                            @if(!$loop->last)
                                <span data-progress-line="{{ $step[0] }}" class="mx-1.5 h-px w-5 bg-white/10 sm:mx-2 sm:w-8"></span>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="mx-auto mt-8 max-w-2xl overflow-hidden rounded-[2rem] border border-white/10 bg-white/[0.035] shadow-2xl shadow-black/25">
                    <div class="flex items-center justify-between border-b border-white/8 px-4 py-3 sm:px-5">
                        <div class="text-right">
                            <p class="text-sm font-bold text-white">Cherry Catch</p>
                            <p class="mt-0.5 text-[11px] text-stone-500">گیلاس‌ها رو بگیر تا امتیازت بالا بره 🍒</p>
                        </div>
                        <div class="rounded-xl border border-white/10 bg-black/15 px-3 py-1.5 text-xs text-stone-300">
                            امتیاز <span data-score class="mr-1 font-black text-rose-100">۰</span>
                        </div>
                    </div>

                    <div id="game-stage"
                         class="relative h-[48svh] min-h-[320px] max-h-[540px] overflow-hidden touch-none bg-[radial-gradient(circle_at_50%_40%,rgba(238,117,210,.12),transparent_42%),linear-gradient(180deg,rgba(117,216,238,.05),transparent_60%)]">
                        <div class="absolute inset-x-8 top-6 flex items-center justify-between text-[10px] uppercase tracking-[0.18em] text-white/25">
                            <span>HOUSE</span><span>GILAS</span>
                        </div>

                        <div data-game-hint class="absolute inset-0 z-10 grid place-items-center pointer-events-none transition-opacity duration-500">
                            <div class="rounded-2xl border border-white/10 bg-black/20 px-4 py-3 text-center backdrop-blur">
                                <p class="text-sm font-semibold text-white">حرکت بده و گیلاس‌ها رو بگیر</p>
                                <p class="mt-1 text-[11px] text-stone-500">با موس، انگشت یا قلم</p>
                            </div>
                        </div>

                        <div data-basket
                             class="absolute bottom-8 left-1/2 z-20 h-11 w-28 -translate-x-1/2 rounded-[1.2rem] border border-white/15 bg-white/10 shadow-[0_20px_45px_rgba(0,0,0,.28)] backdrop-blur-xl sm:w-36">
                            <span class="absolute inset-x-3 -top-1 h-2 rounded-full bg-rose-100/70"></span>
                            <span class="absolute inset-x-4 bottom-2 h-1 rounded-full bg-white/15"></span>
                        </div>

                        <div data-game-items class="absolute inset-0"></div>
                    </div>

                    <div class="grid grid-cols-3 border-t border-white/8">
                        <div class="px-3 py-3 text-center">
                            <p class="text-[10px] text-stone-600">سفارش</p>
                            <p data-order-label class="mt-1 truncate text-xs font-bold text-stone-300">{{ $order->order_number }}</p>
                        </div>
                        <div class="border-x border-white/8 px-3 py-3 text-center">
                            <p class="text-[10px] text-stone-600">وضعیت</p>
                            <p data-mini-status class="mt-1 text-xs font-bold text-amber-100">در حال آماده‌سازی</p>
                        </div>
                        <div class="px-3 py-3 text-center">
                            <p class="text-[10px] text-stone-600">مبلغ</p>
                            <p class="mt-1 text-xs font-bold text-stone-300">{{ number_format((int) $order->total) }} {{ $order->restaurant->currency }}</p>
                        </div>
                    </div>
                </div>

                <div data-ready-panel class="mt-6 hidden rounded-3xl border border-emerald-200/15 bg-emerald-300/5 px-5 py-5 text-center">
                    <p class="text-sm font-bold text-emerald-100">سفارش آماده شد ✨</p>
                    <p class="mt-1 text-xs leading-6 text-emerald-100/60">بازی متوقف شد؛ حالا وقت لذت بردنه.</p>
                    <a href="{{ url('/table/' . request()->cookie('gilas_table_token')) }}"
                       class="mt-4 inline-flex min-h-11 items-center justify-center rounded-xl bg-emerald-100 px-5 text-xs font-bold text-stone-950">
                        بازگشت به منو
                    </a>
                </div>
            </div>
        </section>
    </main>
</div>
@endsection
