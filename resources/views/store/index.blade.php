@extends('layouts.store')

@section('title', ($restaurant?->name ?: 'گیلاس') . ' — منوی غذا و نوشیدنی')
@section('meta-description', 'منوی واقعی ' . ($restaurant?->name ?: 'گیلاس') . '؛ دسته‌بندی، جزئیات و قیمت‌های ثبت‌شده.')
@section('canonical', route('menu.index'))
@section('content')
<main class="store-main gilas-store-menu gilas-page-wrap">
    <section class="gilas-menu-hero">
        <div class="gilas-menu-hero__copy">
            <a class="gilas-breadcrumb" href="{{ route('home') }}">خانه <span aria-hidden="true">/</span> منو</a>
            <span class="gilas-eyebrow"><span class="gilas-eyebrow__line"></span> از آشپزخانهٔ {{ $restaurant->name }}</span>
            <h1>منو را ببین.<br><em>با حوصله انتخاب کن.</em></h1>
            <p>{{ $restaurant->description ?: 'غذاها و نوشیدنی‌های فعال را از اطلاعات واقعی رستوران مرور کن.' }}</p>
            <div class="gilas-menu-hero__meta"><span>{{ $categories->count() }} دستهٔ منو</span><span>قیمت‌ها از دیتابیس رستوران</span></div>
        </div>
        @if($restaurant->cover_image_path)
            <div class="gilas-menu-hero__visual">
                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($restaurant->cover_image_path) }}" alt="{{ $restaurant->name }}" width="1100" height="900" loading="eager" decoding="async">
                <span class="gilas-menu-hero__visual-label">{{ $restaurant->name }}</span>
            </div>
        @else
            <div class="gilas-menu-hero__visual gilas-menu-hero__visual--empty"><span>{{ mb_substr($restaurant->name, 0, 1) }}</span><small>تصویر کاور را از تنظیمات رستوران ثبت کن</small></div>
        @endif
    </section>
    <div class="gilas-store-menu-tools" id="catalog">
        <form class="gilas-store-menu-search" action="{{ route('menu.index') }}" method="GET" role="search">
            <label for="menu-query">جست‌وجو در منو</label>
            <input id="menu-query" type="search" name="q" value="{{ $search }}" placeholder="نام غذا یا نوشیدنی…">
            <label for="menu-sort">مرتب‌سازی</label>
            <select id="menu-sort" name="sort">
                <option value="default" @selected($sort === 'default')>ترتیب منو</option>
                <option value="name" @selected($sort === 'name')>نام غذا</option>
                <option value="price_asc" @selected($sort === 'price_asc')>ارزان‌تر</option>
                <option value="price_desc" @selected($sort === 'price_desc')>گران‌تر</option>
                <option value="newest" @selected($sort === 'newest')>تازه‌تر</option>
            </select>
            <button type="submit" class="gilas-button gilas-button--dark">اعمال <span aria-hidden="true">↗</span></button>
        </form>
    </div>
    <nav class="gilas-menu-filter" aria-label="دسته‌بندی غذاها">
        <a href="{{ route('menu.index', array_filter(['q' => $search, 'sort' => $sort !== 'default' ? $sort : null])) }}" class="{{ blank($selectedSlug) ? 'is-active' : '' }}">همهٔ دسته‌ها</a>
        @foreach($categories as $category)
            <a href="{{ route('menu.category', array_filter(['slug' => $category->slug, 'q' => $search, 'sort' => $sort !== 'default' ? $sort : null])) }}" class="{{ $selectedSlug === $category->slug ? 'is-active' : '' }}">{{ $category->name }}</a>
        @endforeach
    </nav>
    <div class="gilas-menu-results">
        @forelse($menu as $category)
            <section class="gilas-menu-category" id="category-{{ $category->slug }}">
                <div class="gilas-menu-category__heading">
                    <div><span class="gilas-eyebrow"><span class="gilas-eyebrow__line"></span> دستهٔ منو</span><h2>{{ $category->name }}</h2></div>
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
                <h2>{{ $search !== '' ? 'چیزی پیدا نشد.' : 'منو هنوز آماده نشده است.' }}</h2>
                <p>{{ $search !== '' ? 'عبارت دیگری را جست‌وجو کن یا فیلترها را پاک کن.' : 'دسته‌ها و غذاهای منتشرشده از پنل رستوران به‌صورت خودکار اینجا قرار می‌گیرند.' }}</p>
                @if($search !== '')<a class="gilas-button gilas-button--dark" href="{{ route('menu.index') }}">پاک‌کردن جست‌وجو</a>@endif
            </section>
        @endforelse
    </div>
</main>
@endsection
