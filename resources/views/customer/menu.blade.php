@extends('layouts.customer')

@section('title', $restaurant->name . ' — منوی غذا و نوشیدنی')
@section('description', 'منوی آنلاین ' . $restaurant->name . '؛ دسته‌بندی‌ها، جزئیات غذاها و قیمت‌های ثبت‌شده.')

@php
    $menuCover = $restaurant->cover_image_path
        ? \Illuminate\Support\Facades\Storage::disk('public')->url($restaurant->cover_image_path)
        : null;
@endphp

@section('content')
<main class="gilas-menu-page gilas-page-wrap">
    <section class="gilas-menu-hero">
        <div class="gilas-menu-hero__copy">
            <a class="gilas-breadcrumb" href="{{ route('home') }}">خانه <span aria-hidden="true">/</span> منو</a>
            <span class="gilas-eyebrow"><span class="gilas-eyebrow__line"></span> از آشپزخانهٔ {{ $restaurant->name }}</span>
            <h1>منو را ببین.<br><em>با حوصله انتخاب کن.</em></h1>
            <p>{{ $restaurant->description ?: 'غذاها و نوشیدنی‌های فعال را مرور کن و برای دیدن جزئیات هر مورد وارد صفحهٔ آن شو.' }}</p>
            <div class="gilas-menu-hero__meta">
                <span>{{ $categories->count() }} دستهٔ منو</span>
                <span>قیمت‌ها از اطلاعات ثبت‌شدهٔ رستوران</span>
            </div>
        </div>
        @if($menuCover)
            <div class="gilas-menu-hero__visual">
                <img src="{{ $menuCover }}" alt="{{ $restaurant->name }}" width="1100" height="900" loading="eager" decoding="async">
                <span class="gilas-menu-hero__visual-label">{{ $restaurant->name }}</span>
            </div>
        @else
            <div class="gilas-menu-hero__visual gilas-menu-hero__visual--empty">
                <span>{{ mb_substr($restaurant->name, 0, 1) }}</span>
                <small>تصویر کاور را از تنظیمات رستوران ثبت کن</small>
            </div>
        @endif
    </section>

    <nav class="gilas-menu-filter" aria-label="دسته‌بندی غذاها">
        <a href="{{ route('menu.index') }}" class="{{ blank($selectedSlug) ? 'is-active' : '' }}">همهٔ دسته‌ها</a>
        @foreach($categories as $category)
            <a href="{{ route('menu.category', ['slug' => $category->slug]) }}"
               class="{{ $selectedSlug === $category->slug ? 'is-active' : '' }}">
                {{ $category->name }}
            </a>
        @endforeach
    </nav>

    <div class="gilas-menu-results">
        @forelse($menu as $category)
            <section class="gilas-menu-category" id="category-{{ $category->slug }}">
                <div class="gilas-menu-category__heading">
                    <div>
                        <span class="gilas-eyebrow"><span class="gilas-eyebrow__line"></span> دستهٔ منو</span>
                        <h2>{{ $category->name }}</h2>
                    </div>
                    <span class="gilas-menu-category__count">{{ $category->items->count() }} مورد</span>
                </div>

                @if($category->items->isNotEmpty())
                    <div class="public-product-grid">
                        @foreach($category->items as $item)
                            <x-menu-item-card :item="$item" :restaurant="$restaurant" :category-name="$category->name" />
                        @endforeach
                    </div>
                @else
                    <div class="gilas-empty-inline">این دسته هنوز غذای فعالی برای نمایش ندارد.</div>
                @endif
            </section>
        @empty
            <section class="gilas-empty-state">
                <span class="gilas-empty-state__mark">✳</span>
                <h2>منو هنوز آماده نشده است.</h2>
                <p>دسته‌ها و غذاهای منتشرشده از پنل رستوران به‌صورت خودکار اینجا قرار می‌گیرند.</p>
                <a class="gilas-button gilas-button--dark" href="{{ route('public.location') }}">اطلاعات رستوران</a>
            </section>
        @endforelse
    </div>
</main>
@endsection
