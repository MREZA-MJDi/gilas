@extends('layouts.admin')

@section('title', 'داشبورد — ' . $restaurant->name)

@section('content')
<div class="admin-dashboard" data-admin-dashboard>
    <header class="admin-topbar">
        <div>
            <span class="admin-overline">Operations Room</span>
            <h1>{{ $restaurant->name }}</h1>
            <p>نمای زنده‌ی عملیات امروز؛ از سفارش تا تحویل.</p>
        </div>

        <div class="admin-topbar__actions">
            <span class="admin-user">
                <span class="admin-avatar">{{ mb_substr($user?->name ?: 'گ', 0, 1) }}</span>
                <span>
                    <strong>{{ $user?->name ?: 'مدیر' }}</strong>
                    <small>دسترسی فعال</small>
                </span>
            </span>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="admin-ghost-button">خروج</button>
            </form>
        </div>
    </header>

    <section id="overview" class="admin-section admin-hero-grid">
        <div class="admin-card admin-hero">
            <div>
                <span class="admin-card__eyebrow">وضعیت امروز</span>
                <h2>کنترل عملیات، بدون حدس.</h2>
                <p>هر عدد از دیتابیس واقعی می‌آید و وضعیت‌های سفارش مستقیماً از state machine سفارش خوانده می‌شوند.</p>
            </div>
            <div class="admin-hero__status">
                <span class="admin-live-dot"></span>
                <span>سیستم آنلاین</span>
            </div>
        </div>

        <div class="admin-card admin-day-card">
            <span class="admin-card__eyebrow">امروز</span>
            <strong>{{ $dailySales->last()['date'] ?? '—' }}</strong>
            <span>{{ number_format((int) $metrics['ordersToday']) }} سفارش ثبت شده</span>
        </div>
    </section>

    <section class="admin-section">
        <div class="admin-metrics-grid">
            <article class="admin-card admin-metric">
                <span>سفارش امروز</span>
                <strong>{{ number_format((int) $metrics['ordersToday']) }}</strong>
                <small>{{ number_format((int) $metrics['openOrders']) }} باز</small>
            </article>
            <article class="admin-card admin-metric">
                <span>فروش ثبت‌شده</span>
                <strong>{{ number_format((int) $metrics['salesToday']) }}</strong>
                <small>{{ $restaurant->currency }}</small>
            </article>
            <article class="admin-card admin-metric">
                <span>دریافت‌شده</span>
                <strong>{{ number_format((int) $metrics['paidToday']) }}</strong>
                <small>{{ $restaurant->currency }}</small>
            </article>
            <article class="admin-card admin-metric">
                <span>میز درگیر</span>
                <strong>{{ number_format((int) $metrics['activeTables']) }}</strong>
                <small>از {{ number_format($tables->count()) }} میز فعال</small>
            </article>
            <article class="admin-card admin-metric">
                <span>رزرو امروز</span>
                <strong>{{ number_format((int) $metrics['reservationsToday']) }}</strong>
                <small>در انتظار / تأیید / نشسته</small>
            </article>
            <article class="admin-card admin-metric">
                <span>تحویل باز</span>
                <strong>{{ number_format((int) $metrics['openDeliveries']) }}</strong>
                <small>{{ number_format((int) $metrics['availableCouriers']) }} پیک آماده</small>
            </article>
        </div>
    </section>

    <section id="kitchen" class="admin-section admin-work-grid">
        <div class="admin-card admin-panel">
            <div class="admin-panel__head">
                <div>
                    <span class="admin-card__eyebrow">Kitchen Queue</span>
                    <h2>صف آشپزخانه</h2>
                </div>
                <span class="admin-count">{{ number_format($kitchenQueue->count()) }}</span>
            </div>

            <div class="admin-order-stack">
                @forelse($kitchenQueue as $order)
                    @php
                        $statusLabel = match($order->status->value) {
                            'pending' => 'در صف',
                            'confirmed' => 'تأیید شده',
                            'preparing' => 'در حال آماده‌سازی',
                            'ready' => 'آماده',
                            default => $order->status->value,
                        };
                    @endphp
                    <article class="admin-order-row">
                        <div class="admin-order-row__primary">
                            <strong>{{ $order->order_number }}</strong>
                            <span>
                                {{ $order->order_type->value === 'dine_in' ? 'میز ' . ($order->table?->number ?? '—') : ($order->order_type->value === 'delivery' ? 'ارسال' : 'بیرون‌بر') }}
                                · {{ number_format($order->items->sum('quantity')) }} قلم
                            </span>
                        </div>
                        <div class="admin-order-row__meta">
                            <b class="admin-status admin-status--{{ $order->status->value }}">{{ $statusLabel }}</b>
                            <span>{{ number_format((int) $order->total) }} {{ $restaurant->currency }}</span>
                        </div>
                    </article>
                @empty
                    <div class="admin-empty-inline">صف آشپزخانه خالی است.</div>
                @endforelse
            </div>
        </div>

        <div class="admin-card admin-panel" id="cashier">
            <div class="admin-panel__head">
                <div>
                    <span class="admin-card__eyebrow">Sales Pulse</span>
                    <h2>فروش ۷ روز اخیر</h2>
                </div>
                <span class="admin-count">{{ number_format((int) $metrics['categoryCount']) }} دسته</span>
            </div>

            <div class="admin-bars" aria-label="فروش هفت روز اخیر">
                @foreach($dailySales as $day)
                    <div class="admin-bar">
                        <div class="admin-bar__track">
                            <span style="height: {{ max(4, (int) round(($day['revenue'] / $maxDailyRevenue) * 100)) }}%;"></span>
                        </div>
                        <strong>{{ number_format((int) $day['orders']) }}</strong>
                        <small>{{ $day['label'] }}</small>
                    </div>
                @endforeach
            </div>

            <div class="admin-panel__summary">
                <span>مجموع فروش بازه</span>
                <strong>{{ number_format((int) $dailySales->sum('revenue')) }} {{ $restaurant->currency }}</strong>
            </div>
        </div>
    </section>

    <section id="orders" class="admin-section admin-card admin-panel">
        <div class="admin-panel__head">
            <div>
                <span class="admin-card__eyebrow">Live Feed</span>
                <h2>آخرین سفارش‌ها</h2>
            </div>
            <span class="admin-count">{{ number_format($recentOrders->count()) }}</span>
        </div>

        <div class="admin-orders-table">
            <div class="admin-orders-table__head">
                <span>سفارش</span><span>کانال</span><span>مشتری</span><span>وضعیت</span><span>مبلغ</span>
            </div>

            @forelse($recentOrders as $order)
                @php
                    $statusLabel = match($order->status->value) {
                        'pending' => 'در صف',
                        'confirmed' => 'تأیید',
                        'preparing' => 'آماده‌سازی',
                        'ready' => 'آماده',
                        'served' => 'سرو',
                        'out_for_delivery' => 'در مسیر',
                        'delivered' => 'تحویل',
                        'completed' => 'تکمیل',
                        'cancelled' => 'لغو',
                        default => $order->status->value,
                    };
                    $typeLabel = match($order->order_type->value) {
                        'dine_in' => 'میز',
                        'pickup' => 'بیرون‌بر',
                        'delivery' => 'تحویل',
                        default => $order->order_type->value,
                    };
                @endphp
                <article class="admin-orders-table__row">
                    <span><strong>{{ $order->order_number }}</strong><small>{{ $order->items->sum('quantity') }} قلم</small></span>
                    <span>{{ $typeLabel }}{{ $order->table ? ' · ' . $order->table->number : '' }}</span>
                    <span>{{ $order->customer?->name ?: 'مهمان' }}</span>
                    <span><b class="admin-status admin-status--{{ $order->status->value }}">{{ $statusLabel }}</b></span>
                    <strong>{{ number_format((int) $order->total) }} {{ $restaurant->currency }}</strong>
                </article>
            @empty
                <div class="admin-empty-inline">هنوز سفارشی ثبت نشده است.</div>
            @endforelse
        </div>
    </section>

    <section id="tables" class="admin-section admin-card admin-panel">
        <div class="admin-panel__head">
            <div>
                <span class="admin-card__eyebrow">Floor Control</span>
                <h2>میزها و QR</h2>
            </div>
            <span class="admin-count">{{ number_format($tables->count()) }} میز</span>
        </div>

        <div class="admin-tables-grid">
            @foreach($tables as $table)
                @php($occupied = in_array((int) $table->id, $occupiedTableIds, true))
                <article class="admin-table-card {{ $occupied ? 'is-occupied' : '' }}">
                    <div class="admin-table-card__top">
                        <span class="admin-table-number">{{ $table->number }}</span>
                        <span class="admin-table-state">{{ $occupied ? 'درگیر' : 'آزاد' }}</span>
                    </div>
                    <strong>{{ $table->name ?: 'میز ' . $table->number }}</strong>
                    <small>{{ $table->capacity }} نفر · {{ $table->floor ?: 'سالن' }}</small>

                    @if($table->qrCode?->is_active)
                        <a href="{{ route('table.menu', $table->qrCode->token) }}" target="_blank" rel="noopener" class="admin-table-qr">
                            باز کردن QR
                            <span>↗</span>
                        </a>
                    @else
                        <span class="admin-table-qr is-muted">QR فعال نیست</span>
                    @endif
                </article>
            @endforeach
        </div>
    </section>

    <section id="delivery" class="admin-section admin-lower-grid">
        <article class="admin-card admin-action-card">
            <span class="admin-card__eyebrow">Delivery</span>
            <h2>{{ number_format((int) $metrics['openDeliveries']) }} ارسال باز</h2>
            <p>سفارش‌های تحویلی در حال تخصیص، اعزام یا دریافت را یک‌جا ببین.</p>
            <span class="admin-action-card__stat">{{ number_format((int) $metrics['availableCouriers']) }} پیک آماده</span>
        </article>

        <article class="admin-card admin-action-card">
            <span class="admin-card__eyebrow">Menu</span>
            <h2>{{ number_format((int) $metrics['menuCount']) }} آیتم فعال</h2>
            <p>منوی فعلی با {{ number_format((int) $metrics['categoryCount']) }} دسته آماده‌ی مدیریت است.</p>
            <span class="admin-action-card__stat">داده‌ها از دیتابیس واقعی</span>
        </article>

        <article class="admin-card admin-action-card">
            <span class="admin-card__eyebrow">Reservations</span>
            <h2>{{ number_format((int) $metrics['reservationsToday']) }} رزرو امروز</h2>
            <p>صف رزروهای فعال امروز را از همین workspace دنبال کن.</p>
            <span class="admin-action-card__stat">نیازمند workspace رزرو</span>
        </article>
    </section>
</div>
@endsection
