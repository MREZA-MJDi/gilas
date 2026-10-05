@props([
    'item',
    'restaurant',
    'categoryName' => null,
    'interactive' => false,
    'href' => null,
])

@php
    $imageUrl = $item->image_path
        ? \Illuminate\Support\Facades\Storage::disk('public')->url($item->image_path)
        : null;
    $category = $categoryName ?: $item->category?->name;
    $variants = $item->variants ?? collect();
    $options = $item->options ?? collect();
@endphp

<article
    class="menu-card"
    @if($interactive) data-menu-card data-item-id="{{ $item->id }}" data-search="{{ mb_strtolower($item->name . ' ' . ($item->description ?? ''), 'UTF-8') }}" @endif
>
    @if($interactive)
        <button type="button" data-open-item="{{ $item->id }}" class="menu-card__button">
    @else
        <a href="{{ $href ?: route('menu.item', ['slug' => $item->slug]) }}" class="menu-card__button">
    @endif
        <div class="menu-card__media">
            @if($imageUrl)
                <img
                    src="{{ $imageUrl }}"
                    alt="{{ $item->name }}"
                    width="900"
                    height="700"
                    loading="lazy"
                    decoding="async"
                >
            @else
                <div class="menu-card__no-image">تصویر محصول</div>
            @endif
            @if($variants->isNotEmpty() || $options->isNotEmpty())
                <span class="menu-card__badge">قابل شخصی‌سازی</span>
            @endif
            <span class="menu-card__shine" aria-hidden="true"></span>
        </div>

        <div class="menu-card__body">
            <div>
                @if($category)
                    <span class="menu-card__category">{{ $category }}</span>
                @endif
                <h3>{{ $item->name }}</h3>
                @if($item->description)
                    <p>{{ $item->description }}</p>
                @endif
                @if($options->isNotEmpty())
                    <span class="sr-only">گزینه‌ها:
                        @foreach($options as $option)
                            {{ $option->name }}:
                            @foreach($option->values as $value)
                                {{ $value->name }}@if(!$loop->last)، @endif
                            @endforeach
                        @endforeach
                    </span>
                @endif
            </div>

            <div class="menu-card__footer">
                <strong>{{ number_format((int) ($variants->first()?->price ?? $item->price)) }} {{ $restaurant->currency }}</strong>
                <span class="menu-card__cta">{{ $interactive ? 'افزودن' : 'مشاهده' }}</span>
            </div>
        </div>
    @if($interactive)
        </button>
    @else
        </a>
    @endif
</article>