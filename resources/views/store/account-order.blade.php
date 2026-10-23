@extends('layouts.store')

@section('title', 'جزئیات سفارش — ' . ($restaurant?->name ?: 'گیلاس'))
@section('meta-description', 'مشاهده وضعیت سفارش در گیلاس.')

@section('content')
<main class="store-main commerce-page gilas-page-wrap">
    <section class="commerce-empty gilas-order-page-note">
        <span class="commerce-empty__mark">✓</span>
        <p class="store-eyebrow">جزئیات سفارش</p>
        <h1>برای دیدن جزئیات از لینک پیگیری سفارش استفاده کن.</h1>
        <p>پیگیری سفارش با توکن عمومی امن و اختصاصی همان سفارش انجام می‌شود.</p>
        <a class="commerce-button commerce-button--primary" href="{{ route('menu.index') }}">بازگشت به منو ↗</a>
    </section>
</main>
@endsection
