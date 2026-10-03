<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $restaurant->name }} — منوی میز {{ $table->number }}</title>
</head>
<body>
    <main>
        <header>
            <p>{{ $restaurant->name }}</p>
            <h1>منوی میز {{ $table->number }}</h1>
            <p>سفارش شما مستقیماً برای این میز ثبت می‌شود.</p>
        </header>

        @foreach ($menu as $category)
            <section aria-labelledby="category-{{ $category->id }}">
                <h2 id="category-{{ $category->id }}">{{ $category->name }}</h2>

                @forelse ($category->items as $item)
                    <article>
                        <h3>{{ $item->name }}</h3>

                        @if ($item->description)
                            <p>{{ $item->description }}</p>
                        @endif

                        <p>{{ number_format($item->price) }} {{ $restaurant->currency }}</p>

                        @if ($item->variants->isNotEmpty())
                            <h4>انتخاب اندازه</h4>
                            <ul>
                                @foreach ($item->variants as $variant)
                                    <li>
                                        {{ $variant->name }} — {{ number_format($variant->price) }} {{ $restaurant->currency }}
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                        @if ($item->options->isNotEmpty())
                            <h4>افزودنی‌ها و انتخاب‌ها</h4>
                            <ul>
                                @foreach ($item->options as $option)
                                    <li>
                                        <strong>{{ $option->name }}</strong>
                                        @if ($option->is_required)
                                            <span>(الزامی)</span>
                                        @endif
                                        <ul>
                                            @foreach ($option->values as $value)
                                                <li>
                                                    {{ $value->name }}
                                                    @if ($value->price_delta > 0)
                                                        (+{{ number_format($value->price_delta) }} {{ $restaurant->currency }})
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </article>
                @empty
                    <p>در این دسته آیتم فعالی وجود ندارد.</p>
                @endforelse
            </section>
        @endforeach
    </main>
</body>
</html>
