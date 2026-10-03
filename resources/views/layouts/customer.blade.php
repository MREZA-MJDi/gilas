<!doctype html>
<html lang="fa" dir="rtl" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0b090d">
    <title>@yield('title', 'گیلاس')</title>
    <meta name="description" content="@yield('description', 'منوی دیجیتال و سفارش آنلاین گیلاس')">
    @unless(app()->environment('testing'))
        @vite(['resources/css/customer.css', 'resources/js/customer.js'])
    @endunless
</head>
<body class="gilas-public customer-page min-h-screen text-stone-100 antialiased">
    @include('components.footsteps')
    <div class="gilas-content">
        @yield('content')
    </div>
</body>
</html>
