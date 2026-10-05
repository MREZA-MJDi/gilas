@extends('layouts.admin')

@section('title', $restaurant->name . ' — داشبورد')

@section('content')
<div class="admin-page-head">
    <div>
        <p class="admin-kicker">اتاق عملیات</p>
        <h1>{{ $restaurant->name }}</h1>
        <p>{{ $user->name }} · نمای امروز کسب‌وکار</p>
    </div>
    <form method="POST" action="{{ route('admin.logout') }}">@csrf<button class="admin-logout">خروج</button></form>
</div>

<section class="admin-metrics">
    @foreach([
        ['سفارش امروز',$metrics['ordersToday']],
        ['فروش امروز',number_format($metrics['revenueToday']).' '.$restaurant->currency],
        ['پرداخت‌شده',number_format($metrics['paidToday']).' '.$restaurant->currency],
        ['سفارش باز',$metrics['openOrders']],
        ['میز مشغول',$metrics['activeTables']],
        ['منوی فعال',$metrics['menuCount']],
        ['رزرو امروز',$metrics['reservationsToday']],
        ['تحویل باز',$metrics['openDeliveries']],
    ] as [$label,$value])
        <article class="admin-metric"><span>{{ $label }}</span><strong>{{ $value }}</strong></article>
    @endforeach
</section>

<section class="admin-grid admin-grid--main">
    <article class="admin-panel" id="orders">
        <div class="admin-panel__head"><div><span class="admin-kicker">آخرین سفارش‌ها</span><h2>Order stream</h2></div><span>{{ $recentOrders->count() }} مورد</span></div>
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead><tr><th>شماره</th><th>مشتری</th><th>میز</th><th>مبلغ</th><th>وضعیت</th></tr></thead>
                <tbody>
                @forelse($recentOrders as $order)
                    <tr>
                        <td>{{ $order->order_number }}</td>
                        <td>{{ $order->customer?->name ?: 'مشتری میز' }}</td>
                        <td>{{ $order->table?->number ?: '—' }}</td>
                        <td>{{ number_format((int) $order->total) }}</td>
                        <td><span class="admin-status">{{ method_exists($order->status, 'label') ? $order->status->label() : $order->status->value }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="5">هنوز سفارشی ثبت نشده است.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </article>

    <article class="admin-panel" id="kitchen">
        <div class="admin-panel__head"><div><span class="admin-kicker">آشپزخانه</span><h2>Kitchen queue</h2></div><span>{{ $kitchenQueue->count() }} در صف</span></div>
        <div class="admin-queue">
            @forelse($kitchenQueue as $order)
                <div class="admin-queue__item">
                    <div><strong>{{ $order->order_number }}</strong><span>{{ $order->table?->number ? 'میز '.$order->table->number : 'بیرون‌بر' }}</span></div>
                    <b>{{ $order->items->count() }} آیتم</b>
                </div>
            @empty
                <p>صف آشپزخانه خلوت است.</p>
            @endforelse
        </div>
    </article>
</section>

<section class="admin-panel" id="tables">
    <div class="admin-panel__head"><div><span class="admin-kicker">Floor view</span><h2>میزها و QR</h2></div><span>{{ $tables->count() }} میز فعال</span></div>
    <div class="admin-tables-grid">
        @foreach($tables as $table)
            <article class="admin-table-card {{ in_array($table->id, $occupiedTableIds, true) ? 'is-occupied' : '' }}">
                <span>میز {{ $table->number }}</span>
                <strong>{{ in_array($table->id, $occupiedTableIds, true) ? 'مشغول' : 'آزاد' }}</strong>
                <small>{{ $table->capacity }} نفر · {{ $table->qrCode?->is_active ? 'QR فعال' : 'QR غیرفعال' }}</small>
            </article>
        @endforeach
    </div>
</section>

<section class="admin-panel" id="sales">
    <div class="admin-panel__head"><div><span class="admin-kicker">۷ روز اخیر</span><h2>Sales rhythm</h2></div></div>
    <div class="admin-sales">
        @php($maxRevenue = max(1, collect($dailySales)->max('revenue')))
        @foreach($dailySales as $day)
            <div class="admin-sales__day">
                <span>{{ $day['label'] }}</span>
                <div class="admin-sales__bar"><i style="height:{{ min(100, max(6, ($day['revenue'] / $maxRevenue) * 100)) }}%"></i></div>
                <small>{{ number_format($day['revenue']) }}</small>
            </div>
        @endforeach
    </div>
</section>
@endsection