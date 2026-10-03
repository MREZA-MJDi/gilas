<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0b090d">
    <title>@yield('title', 'پنل مدیریت خانه گیلاسی')</title>
    @unless(app()->environment('testing'))
        @vite(['resources/css/admin.css', 'resources/js/admin.js'])
    @endunless
</head>
<body class="admin-page">
<div class="admin-shell">
    <aside class="admin-sidebar">
        <div class="flex items-center gap-3">
            <div class="grid size-11 place-items-center rounded-2xl border border-white/10 bg-white/5 text-lg font-black text-rose-100">گ</div>
            <div class="admin-nav-label min-w-0">
                <p class="truncate text-sm font-bold text-white">خانه گیلاسی</p>
                <p class="truncate text-xs text-stone-500">Operations Room</p>
            </div>
        </div>
        <nav class="mt-8 space-y-2" aria-label="ناوبری مدیریت">
            @foreach([['داشبورد','overview','is-active'],['سفارش‌ها','orders',''],['آشپزخانه','kitchen',''],['میزها و QR','tables',''],['منو و غذاها','menu',''],['تحویل','delivery',''],['صندوق','cashier','']] as [$label,$icon,$active])
                <a href="#" class="flex min-h-11 items-center gap-3 rounded-xl px-3 text-sm font-semibold {{ $active ? 'bg-rose-200/10 text-white' : 'text-stone-400 hover:bg-white/5 hover:text-white' }}">
                    <span class="grid size-8 shrink-0 place-items-center rounded-lg bg-white/5 text-xs">{{ strtoupper(substr($icon,0,1)) }}</span>
                    <span class="admin-nav-label">{{ $label }}</span>
                </a>
            @endforeach
        </nav>
    </aside>
    <main class="admin-main">@yield('content')</main>
    <nav class="admin-mobile-nav" aria-label="ناوبری سریع">
        <a href="#" class="is-active" aria-label="داشبورد">⌂</a>
        <a href="#" aria-label="سفارش‌ها">◉</a>
        <a href="#" aria-label="آشپزخانه">◈</a>
        <a href="#" aria-label="میزها">▦</a>
    </nav>
</div>
</body>
</html>