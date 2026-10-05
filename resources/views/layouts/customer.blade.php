<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#F7F3ED">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <title>@yield('title', 'خانه گیلاسی')</title>
    <meta name="description" content="@yield('description', 'منوی دیجیتال و سفارش آنلاین خانه گیلاسی')">
    @unless(app()->environment('testing'))
        @vite(['resources/css/customer.css', 'resources/js/customer.js'])
    @endunless
</head>
<body class="gilas-public customer-page">
<div class="gilas-content">@yield('content')</div>
<footer class="gilas-site-footer" aria-label="پاورقی گیلاس">
    <div class="ui-shell gilas-site-footer__inner">
        <a href="{{ route('home') }}" class="gilas-site-footer__brand"><img src="{{ asset('favicon.svg') }}" alt="" width="36" height="36"><span>خانه گیلاسی</span></a>
        <nav class="gilas-site-footer__nav"><a href="{{ route('menu.index') }}">منو</a><a href="{{ route('public.experience') }}">تجربه</a><a href="{{ route('public.location') }}">مسیریابی</a><a href="{{ route('public.story') }}">داستان</a></nav>
    </div>
</footer>
</body>
</html>
