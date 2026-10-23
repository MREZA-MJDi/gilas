@extends('layouts.store')
@section('title', 'ثبت سفارش میز — ' . ($restaurant?->name ?: 'گیلاس'))
@section('meta-description', 'ثبت سفارش از مسیر امن و متصل به QR میز در گیلاس.')
@section('content')
<main class="store-main commerce-page gilas-page-wrap">
    <section class="commerce-empty gilas-order-page-note">
        <span class="commerce-empty__mark">✓</span><p class="store-eyebrow">مرحلهٔ پایانی</p>
        <h1>ثبت سفارش از سبد QR میز انجام می‌شود.</h1>
        <p>قیمت‌ها، گزینه‌ها و موجودی در سرور دوباره بررسی می‌شوند؛ مسیر عمومی خرید فروشگاهی جدا از سیستم سفارش میز ساخته نشده است.</p>
        <a class="commerce-button commerce-button--primary" href="{{ route('cart.index') }}">بازگشت به سبد سفارش ↗</a>
    </section>
</main>
@endsection
