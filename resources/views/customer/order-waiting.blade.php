@extends('layouts.customer')

@section('title', 'پیگیری سفارش ' . $order->order_number . ' — ' . $order->restaurant->name)
@section('description', 'وضعیت لحظه‌ای سفارش ' . $order->order_number . ' در ' . $order->restaurant->name)

@php
    $initialStatusLabel = match ($order->status->value) {
        'pending' => 'در صف بررسی',
        'confirmed' => 'سفارش تأیید شد',
        'preparing' => 'در حال آماده‌سازی',
        'ready' => 'آماده شد',
        'served' => 'سرو شد',
        'out_for_delivery' => 'در مسیر',
        'delivered' => 'تحویل شد',
        'completed' => 'تکمیل شد',
        'cancelled' => 'لغو شد',
        default => 'در حال بررسی',
    };
    $firstOrderItem = $order->items->first();
    $dishImagePath = $firstOrderItem?->menuItem?->image_path ?: $order->restaurant->cover_image_path;
    $dishImageUrl = $dishImagePath
        ? \Illuminate\Support\Facades\Storage::disk('public')->url($dishImagePath)
        : null;
    $tableMenuUrl = $order->table?->qrCode?->token
        ? route('table.menu', ['token' => $order->table->qrCode->token])
        : route('menu.index');
@endphp

@section('content')
<div id="order-waiting"
     data-status-url="{{ $statusUrl }}"
     data-order-number="{{ $order->order_number }}"
     data-currency="{{ $order->restaurant->currency }}"
     data-initial-status="{{ $order->status->value }}"
     class="order-status-page">
    <main class="order-status-wrap">
        <section class="order-status-card">
            <header class="order-status-card__head">
                <div>
                    <p>پیگیری سفارش · {{ $order->order_number }}</p>
                    <strong>{{ $order->restaurant->name }}
                        @if($order->table)
                            <span> · میز {{ $order->table->number }}</span>
                        @endif
                    </strong>
                </div>
                <a href="{{ $tableMenuUrl }}" class="gilas-button gilas-button--line">بازگشت به منو <span aria-hidden="true">←</span></a>
            </header>

            <section class="order-status-content">
                <span id="status-badge" class="order-status-badge"><span data-status-label>{{ $initialStatusLabel }}</span></span>
                <h1>سفارشت در جریان است.</h1>
                <p>وضعیت سفارش هر چند ثانیه به‌صورت خودکار به‌روز می‌شود. نیازی به تازه‌سازی دستی صفحه نیست.</p>

                <div class="order-status-progress" aria-label="مراحل سفارش">
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
                            <div data-progress-step="{{ $step[0] }}" class="grid size-8 place-items-center rounded-full border text-[10px] font-bold sm:size-9">
                                {{ $loop->iteration }}
                            </div>
                            @if(!$loop->last)
                                <span data-progress-line="{{ $step[0] }}" class="mx-1.5 h-px w-5 sm:mx-2 sm:w-8"></span>
                            @endif
                        </div>
                    @endforeach
                </div>

                <section class="order-status-visual" aria-live="polite">
                    <div class="order-status-visual__steam" aria-hidden="true"><i></i><i></i><i></i></div>
                    <div class="order-status-visual__plate">
                        @if($dishImageUrl)
                            <img src="{{ $dishImageUrl }}" alt="{{ $firstOrderItem?->name ?: $order->restaurant->name }}" width="800" height="800" loading="eager" decoding="async">
                        @else
                            <span aria-hidden="true">{{ mb_substr($firstOrderItem?->name ?: $order->restaurant->name, 0, 1) }}</span>
                        @endif
                    </div>
                    <div class="order-status-visual__copy">
                        <small>از آشپزخانهٔ {{ $order->restaurant->name }}</small>
                        <strong data-visual-status>{{ $initialStatusLabel }}</strong>
                        <span>{{ $firstOrderItem?->name ?: 'با دقت آماده می‌شود' }}</span>
                    </div>
                </section>

                <div class="order-game-meta">
                    <div><p>شماره سفارش</p><strong data-order-label>{{ $order->order_number }}</strong></div>
                    <div><p>وضعیت فعلی</p><strong data-mini-status>{{ $initialStatusLabel }}</strong></div>
                    <div><p>مبلغ سفارش</p><strong>{{ number_format((int) $order->total) }} {{ $order->restaurant->currency }}</strong></div>
                </div>

                <div data-ready-panel class="order-ready-panel" hidden>
                    <p data-ready-title>سفارش آماده شد ✨</p>
                    <small data-ready-copy>از غذایت لذت ببر؛ از همراهی‌ات ممنونیم.</small>
                    <a href="{{ $tableMenuUrl }}" class="gilas-button gilas-button--dark mt-4">بازگشت به منو <span aria-hidden="true">←</span></a>
                </div>
            </section>
        </section>
    </main>
</div>
@endsection
