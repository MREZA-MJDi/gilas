@extends('layouts.store')

@section('title', 'سبد سفارش — ' . ($restaurant?->name ?: 'گیلاس'))
@section('meta-description', 'مرور سبد سفارش میز در منوی QR گیلاس.')

@section('content')
<main class="store-main commerce-page gilas-page-wrap">
    <section class="commerce-empty gilas-order-page-note">
        <span class="commerce-empty__mark">گ</span>
        <p class="store-eyebrow">سفارش میز</p>
        <h1>سبد سفارش از QR میز باز می‌شود.</h1>
        <p>در گیلاس، سفارش غذا باید به یک میز معتبر متصل باشد. اگر QR میز را قبلاً اسکن کرده‌ای، همین صفحه تو را به منوی همان میز و سبد فعلی برمی‌گرداند.</p>
        <a class="commerce-button commerce-button--primary" href="{{ route('menu.index') }}">دیدن منوی رستوران ↗</a>
    </section>
</main>
@endsection
