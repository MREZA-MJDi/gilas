@extends('layouts.store')

@section('title', 'حساب مشتری — ' . ($restaurant?->name ?: 'گیلاس'))
@section('meta-description', 'اطلاعات حساب مشتری در نسخهٔ فعلی گیلاس.')

@section('content')
<main class="store-main commerce-page gilas-page-wrap">
    <section class="commerce-empty gilas-order-page-note">
        <span class="commerce-empty__mark">گ</span>
        <p class="store-eyebrow">باشگاه گیلاس</p>
        <h1>ثبت‌نام حساب مشتری هنوز فعال نیست.</h1>
        <p>سفارش سر میز از طریق QR انجام می‌شود و هر سفارش لینک پیگیری اختصاصی دارد. این صفحه ثبت‌نام ساختگی یا غیرفعال ارائه نمی‌کند.</p>
        <a class="commerce-button commerce-button--primary" href="{{ route('menu.index') }}">دیدن منو ↗</a>
    </section>
</main>
@endsection
