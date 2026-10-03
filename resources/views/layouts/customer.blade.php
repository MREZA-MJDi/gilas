<!doctype html>
<html lang="fa" dir="rtl" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#120f0d">
    <title>@yield('title', 'منوی خانه گیلاسی')</title>
    <meta name="description" content="@yield('description', 'منوی دیجیتال و سفارش آنلاین خانه گیلاسی')">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Estedad:wght@400;500;600;700&display=swap" rel="stylesheet">
    @unless(app()->environment('testing'))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endunless
</head>
<body class="min-h-screen bg-[#120f0d] text-stone-100 antialiased">
    <svg class="footsteps-layer" data-footsteps viewBox="0 0 0 0" aria-hidden="true" focusable="false">
    <defs>
        <path id="feet-shape"
              d="M41.5,30.2C36,24.6,4.7,26.1,7.7,49.4c.8,5.9,4,10.2,8,19.9,3,7.2-.1,15.7,5.8,20.8S43,91.6,38.6,75.9c-1.8-6.5-7.6-9.3-8.9-14.1C26.1,47.9,51.7,40.6,41.5,30.2Z M41.7,7.6c-2.6-.3-5.2,2.8-5.6,7s1.3,7.8,3.9,8.1,5.2-2.9,5.6-7.1S44.4,7.8,41.7,7.6Z M28.8,21.9c2.2.2,4.1-2.1,4.5-5.1s-1.1-5.7-3.2-6-4.1,2.1-4.5,5.1S26.7,21.6,28.8,21.9Z M20.1,23.3c1.6.1,3.1-1.8,3.4-4.4s-.8-4.7-2.4-4.9-3,1.8-3.3,4.3S18.5,23.1,20.1,23.3Z M14.9,25.5c1.4-.2,2.4-1.9,2.1-3.9s-1.6-3.4-3-3.3-2.4,2-2.2,3.9S13.4,25.7,14.9,25.5Z M10.9,29.2c1-.1,1.7-1.4,1.5-2.8s-1.1-2.5-2.2-2.4-1.7,1.4-1.6,2.8S9.8,29.3,10.9,29.2Z"/>
        <symbol id="feet-left" viewBox="0 0 100 100"><rect width="100" height="100" fill="none"/><use href="#feet-shape"/></symbol>
        <symbol id="feet-right" viewBox="0 0 100 100"><rect width="100" height="100" fill="none"/><g transform="scale(-1 1) translate(-100 0)"><use href="#feet-shape"/></g></symbol>
    </defs>
</svg>
    @yield('content')
</body>
</html>