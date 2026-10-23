@extends('layouts.store')
@section('title', 'پیگیری سفارش — ' . ($restaurant?->name ?: 'گیلاس'))
@section('meta-description', 'جزئیات سفارش ثبت‌شده در گیلاس.')
@section('content')
<main class="store-main commerce-page gilas-page-wrap">
    <section class="commerce-complete commerce-panel">
        <span class="commerce-complete__mark" aria-hidden="true">✓</span><p class="store-eyebrow">سفارش دریافت شد</p>
        <h1>سفارش شما ثبت شده است.</h1><p>شماره سفارش: <strong>{{ $order->order_number }}</strong></p>
        <div class="commerce-summary__lines">
            <div><span>وضعیت سفارش</span><strong>{{ $order->status->value }}</strong></div>
            <div><span>جمع اقلام</span><strong>{{ number_format((int) $order->subtotal) }} {{ $order->restaurant?->currency }}</strong></div>
            <div><span>مالیات</span><strong>{{ number_format((int) $order->tax) }} {{ $order->restaurant?->currency }}</strong></div>
            <div class="commerce-summary__total"><span>مبلغ نهایی</span><strong>{{ number_format((int) $order->total) }} {{ $order->restaurant?->currency }}</strong></div>
        </div>
        <div class="commerce-complete__items">
            @foreach($order->items as $item)<div><span>{{ $item->name }} × {{ (int) $item->quantity }}</span><strong>{{ number_format((int) $item->total_price) }} {{ $order->restaurant?->currency }}</strong></div>@endforeach
        </div>
        <div class="commerce-complete__actions">
            <a class="commerce-button commerce-button--primary" href="{{ route('customer.orders.show', $order->public_token) }}">پیگیری سفارش ↗</a>
            <a class="commerce-button commerce-button--quiet" href="{{ route('home') }}">بازگشت به خانه</a>
        </div>
    </section>
</main>
@endsection
