@extends('layouts.admin')

@section('title', 'داشبورد — ' . $restaurant->name)

@section('body')
<div class="admin-shell">
    <aside class="admin-sidebar">
        <div class="admin-brand"><div class="admin-brand__mark">گ</div><div><strong>{{ $restaurant->name }}</strong><small>Operations Room</small></div></div>
        <nav class="admin-nav" aria-label="ناوبری مدیریت">
            <a class="is-active" href="{{ route('admin.dashboard', $restaurant) }}"><span>⌂</span><span>داشبورد</span></a>
            <a href="#"><span>◉</span><span>سفارش‌ها</span></a>
            <a href="#"><span>◈</span><span>آشپزخانه</span></a>
            <a href="#"><span>▦</span><span>میزها و QR</span></a>
            <a href="{{ route('menu.index') }}"><span>☕</span><span>منوی مشتری</span></a>
        </nav>
    </aside>
    <main class="admin-main">
        <header class="admin-topbar">
            <div class="admin-title"><h1>سلام {{ $user->name ?: 'مدیر' }} 👋</h1><p>نمای سریع وضعیت خانه گیلاسی برای امروز.</p></div>
            <form method="POST" action="{{ route('admin.logout') }}">@csrf<button class="admin-logout" type="submit">خروج</button></form>
        </header>

        <section class="admin-data-grid" aria-label="شاخص‌های امروز">
            <article class="admin-stat"><small>سفارش امروز</small><strong>{{ number_format($metrics['ordersToday']) }}</strong></article>
            <article class="admin-stat"><small>فروش امروز</small><strong>{{ number_format($metrics['revenueToday']) }}</strong></article>
            <article class="admin-stat"><small>سفارش باز</small><strong>{{ number_format($metrics['openOrders']) }}</strong></article>
            <article class="admin-stat"><small>میز فعال</small><strong>{{ number_format($metrics['activeTables']) }}</strong></article>
            <article class="admin-stat"><small>آیتم منو</small><strong>{{ number_format($metrics['menuCount']) }}</strong></article>
            <article class="admin-stat"><small>دسته منو</small><strong>{{ number_format($metrics['categoryCount']) }}</strong></article>
            <article class="admin-stat"><small>رزرو امروز</small><strong>{{ number_format($metrics['reservationsToday']) }}</strong></article>
            <article class="admin-stat"><small>پرداخت‌شده</small><strong>{{ number_format($metrics['paidToday']) }}</strong></article>
        </section>

        <section class="admin-panels">
            <article class="admin-panel">
                <h2>آخرین سفارش‌ها</h2>
                <p>داده‌ی واقعی همین رستوران؛ بدون mock.</p>
                <div class="admin-orders">
                    @forelse($recentOrders as $order)
                        <div class="admin-order-row">
                            <div><strong>{{ $order->order_number }}</strong><span> · {{ $order->customer?->name ?: 'مهمان' }} @if($order->table) · میز {{ $order->table->number }} @endif</span></div>
                            <span>{{ number_format((int) $order->total) }}</span>
                            <span class="admin-badge">{{ $order->status->label() }}</span>
                        </div>
                    @empty
                        <div class="admin-empty">هنوز سفارشی ثبت نشده است.</div>
                    @endforelse
                </div>
            </article>
            <article class="admin-panel">
                <h2>مسیرهای سریع</h2>
                <p>کارهای پرتکرار باید یک لمس فاصله داشته باشند.</p>
                <div class="admin-orders">
                    <div class="admin-order-row"><strong>سفارش‌ها</strong><a class="admin-badge" href="#">باز کردن</a></div>
                    <div class="admin-order-row"><strong>میزها و QR</strong><a class="admin-badge" href="#">باز کردن</a></div>
                    <div class="admin-order-row"><strong>منوی مشتری</strong><a class="admin-badge" href="{{ route('menu.index') }}">مشاهده</a></div>
                </div>
            </article>
        </section>
    </main>
    <nav class="admin-mobile-nav" aria-label="ناوبری سریع">
        <a class="is-active" href="{{ route('admin.dashboard', $restaurant) }}">داشبورد</a>
        <a href="#">سفارش</a><a href="#">آشپزخانه</a><a href="#">میزها</a>
    </nav>
</div>
@endsection
