@extends('layouts.customer')

@section('title', $item->name . ' — ' . $restaurant->name)
@section('description', $item->description ?: 'جزئیات ' . $item->name)

@section('content')
<main class="ui-shell public-item-shell">
    <a href="{{ route('menu.index') }}" class="public-info__back">← بازگشت به منو</a>

    <article class="public-item-detail">
        <div class="public-item-detail__media">
            @if($item->image_path)
                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($item->image_path) }}"
                     alt="{{ $item->name }}" width="1200" height="900" loading="eager" decoding="async">
            @else
                <div class="menu-card__no-image">تصویر محصول در دسترس نیست</div>
            @endif
        </div>
        <div class="public-item-detail__content">
            <span class="ui-kicker">{{ $item->category?->name ?: 'منوی گیلاس' }}</span>
            <h1>{{ $item->name }}</h1>
            @if($item->description)<p>{{ $item->description }}</p>@endif
            <div class="sheet-price"><span>شروع قیمت</span><strong>{{ number_format((int) $item->price) }} {{ $restaurant->currency }}</strong></div>

            @if($item->variants->isNotEmpty())
                <section class="detail-group">
                    <h2>اندازه‌ها</h2>
                    <div class="detail-options">
                        @foreach($item->variants as $variant)
                            <div><strong>{{ $variant->name }}</strong><span>{{ number_format((int) $variant->price) }} {{ $restaurant->currency }}</span></div>
                        @endforeach
                    </div>
                </section>
            @endif

            @if($item->options->isNotEmpty())
                <section class="detail-group">
                    <h2>شخصی‌سازی</h2>
                    <div class="detail-options">
                        @foreach($item->options as $option)
                            <div><strong>{{ $option->name }}</strong><span>{{ $option->values->pluck('name')->join(' · ') }}</span></div>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    </article>
</main>
@endsection
