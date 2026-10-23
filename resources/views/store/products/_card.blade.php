@php
    $image = $item->image_path
        ? \Illuminate\Support\Facades\Storage::disk('public')->url($item->image_path)
        : null;
    $price = (int) ($item->variants->first()?->price ?? $item->price);
@endphp
<article class="menu-card">
    <a class="menu-card__button" href="{{ route('menu.item', ['slug' => $item->slug]) }}" aria-label="جزئیات {{ $item->name }}">
        <div class="menu-card__media">
            @if($image)
                <img src="{{ $image }}" alt="{{ $item->name }}" loading="lazy" decoding="async">
            @else
                <span class="menu-card__no-image">تصویر غذا ثبت نشده</span>
            @endif
            <span class="menu-card__shine" aria-hidden="true"></span>
        </div>
        <div class="menu-card__body">
            <div><span class="menu-card__category">{{ $item->category?->name ?: 'منوی گیلاس' }}</span><h3>{{ $item->name }}</h3>@if($item->description)<p>{{ $item->description }}</p>@endif</div>
            <div class="menu-card__footer"><strong>{{ number_format($price) }} {{ $restaurant->currency }}</strong><span class="menu-card__cta">جزئیات غذا</span></div>
        </div>
    </a>
</article>
