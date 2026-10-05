@props(['products' => collect(), 'label' => 'پیشنهاد خانه گیلاسی', 'currency' => 'تومان'])

@php
    $items = collect($products)->values()->map(function ($item) use ($currency) {
        $image = $item->image_path
            ? IlluminateSupportFacadesStorage::url($item->image_path)
            : null;

        return [
            'id' => $item->id,
            'name' => $item->name,
            'category' => $item->category?->name,
            'description' => $item->description,
            'image' => $image,
            'price_label' => number_format((int) $item->price) . ' ' . $currency,
            'url' => route('menu.item', ['slug' => $item->slug]),
        ];
    })->filter(fn ($item) => filled($item['image']))->values();
@endphp

<section class="gilas-product" data-gilas-product-card
         data-products='{{ $items->toJson(JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_APOS | JSON_HEX_QUOT) }}'
         aria-label="{{ $label }}">
    <div class="gilas-product__stage" data-product-stage tabindex="0">
        <div class="gilas-product__ambient gilas-product__ambient--a" aria-hidden="true"></div>
        <div class="gilas-product__ambient gilas-product__ambient--b" aria-hidden="true"></div>
        <div class="gilas-product__stack">
            <div class="gilas-product__image-container" data-image-container>
                <div class="gilas-product__layers" data-product-layer-stack>
                    @for($i = 0; $i < 10; $i++)
                        <div class="gilas-product__layer rectangle" data-product-layer></div>
                    @endfor
                </div>
            </div>
        </div>
        <div class="gilas-product__controls">
            <div class="gilas-product__shapes" aria-label="حالت تصویر">
                <button type="button" class="gilas-product__shape is-active" data-shape="rectangle" aria-label="مستطیل"></button>
                <button type="button" class="gilas-product__shape" data-shape="circle" aria-label="دایره"></button>
                <button type="button" class="gilas-product__shape" data-shape="diamond" aria-label="لوزی"></button>
                <button type="button" class="gilas-product__shape" data-shape="hexagon" aria-label="شش ضلعی"></button>
            </div>
            <div class="gilas-product__arrows">
                <button class="gilas-product__arrow" type="button" data-product-next aria-label="محصول بعدی">←</button>
                <button class="gilas-product__arrow" type="button" data-product-prev aria-label="محصول قبلی">→</button>
            </div>
        </div>
        <div class="gilas-product__dots" data-product-dots aria-label="محصولات"></div>
    </div>
    <div class="gilas-product__details">
        <div class="gilas-product__topline"><span data-product-category>{{ $label }}</span><span data-product-position>01 / 01</span></div>
        <h2 class="gilas-product__name" data-product-name>...</h2>
        <p class="gilas-product__description" data-product-description>...</p>
        <div class="gilas-product__meta">
            <strong class="gilas-product__price" data-product-price></strong>
            <a class="gilas-product__cta" data-product-action href="{{ route('menu.index') }}">انتخاب این طعم <span aria-hidden="true">↗</span></a>
        </div>
        <div class="gilas-product__hint"><span>حرکت موس یا لمس</span><span>·</span><span>چرخش نرم سه‌بعدی</span></div>
    </div>
</section>
