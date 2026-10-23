@extends('layouts.store')
@section('title', 'ورود مدیر و کارکنان — ' . ($restaurant?->name ?: 'گیلاس'))
@section('meta-description', 'ورود امن مدیر و کارکنان به پنل رستوران گیلاس.')
@section('content')
<main class="store-main commerce-page gilas-page-wrap">
    <section class="commerce-empty gilas-order-page-note">
        <span class="commerce-empty__mark">گ</span><p class="store-eyebrow">حساب کارکنان</p>
        <h1>ورود مشتریان در این نسخه فعال نیست.</h1>
        <p>برای مدیریت رستوران از صفحهٔ ورود اختصاصی مدیر و کارکنان استفاده کن.</p>
        <a class="commerce-button commerce-button--primary" href="{{ route('admin.login') }}">ورود به پنل مدیریت ↗</a>
    </section>
</main>
@endsection
