@extends('layouts.store')
@section('title', 'پیگیری سفارش — ' . ($restaurant?->name ?: 'گیلاس'))
@section('meta-description', 'پیگیری سفارش ثبت‌شده در گیلاس.')
@section('content')
<main class="store-main commerce-page gilas-page-wrap">
    <section class="commerce-empty gilas-order-page-note">
        <span class="commerce-empty__mark">گ</span><p class="store-eyebrow">پیگیری سفارش</p>
        <h1>سفارش‌ها با لینک پیگیری خودشان باز می‌شوند.</h1>
        <p>در بک‌اند فعلی Gilas حساب مشتری و تاریخچهٔ کاربری عمومی وجود ندارد. پس از ثبت سفارش با QR میز، از لینک اختصاصی همان سفارش برای مشاهدهٔ وضعیت استفاده کن.</p>
        <a class="commerce-button commerce-button--primary" href="{{ route('menu.index') }}">رفتن به منو ↗</a>
    </section>
</main>
@endsection
