@extends('layouts.customer')

@section('title', $item->name . ' — ' . $restaurant->name)
@section('description', $item->description ?: 'جزئیات ' . $item->name . ' از منوی ' . $restaurant->name)

@section('content')
<main class="ui-shell public-item-shell">
    <a href="{{ route('menu.index') }}" class="public-page-back">→ بازگشت به منو</a>

    <article class="public-item">
        <div class="public-item__media">
            @if($item->image_path)
                <img
                    src="{{ \\Illuminate\\Support\\Facades\\Storage::url($item->image_path) }}"
                    alt="{{ $item->name }}"
                    width="1200"
                    height="1200"
                    loading="eager"
                    decoding="async">
            @else
                <span aria-hidden="true">گ</span>
            @endif
        </div>

        <div class="public-item__content">
            <span class="eyebrow">{{ $item->category?->name ?: 'گیلاس' }}</span>
            <h1>{{ $item->name }}</h1>

            @if($item->description)
                <p class="public-item__description">{{ $item->description }}</p>
            @endif

            <div class="public-item__price">
                <span>شروع قیمت</span>
                <strong>{{ number_format((int) $item->price) }} {{ $restaurant->currency }}</strong>
            </div>

            @if($item->variants->isNotEmpty())
                <section class="public-item__choice">
                    <div>
                        <span class="eyebrow">انتخاب</span>
                        <h2>اندازه</h2>
                    </div>
                    <div class="public-item__options">
                        @foreach($item->variants as $variant)
                            <div class="public-item__option">
                                <strong>{{ $variant->name }}</strong>
                                <span>{{ number_format((int) $variant->price) }} {{ $restaurant->currency }}</span>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            @if($item->options->isNotEmpty())
                <section class="public-item__choice">
                    <div>
                        <span class="eyebrow">شخصی‌سازی</span>
                        <h2>انتخاب‌های این آیتم</h2>
                    </div>
                    <div class="public-item__options">
                        @foreach($item->options as $option)
                            <div class="public-item__option public-item__option--stack">
                                <strong>{{ $option->name }}</strong>
                                <span>
                                    @foreach($option->values as $value)
                                        {{ $value->name }}{{ !$loop->last ? ' · ' : '' }}
                                    @endforeach
                                </span>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            <a class="landing-button public-item__cta" href="{{ route('menu.index') }}">
                انتخاب از منو
            </a>
        </div>
    </article>
</main>
@endsection
