<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#0b090d">
    <meta name="description" content="خانه گیلاسی — تجربه‌ای برای دیدن، انتخاب کردن و ماندن.">
    <title>خانه گیلاسی</title>
    @unless(app()->environment('testing'))
        @vite(['resources/css/landing.css', 'resources/js/landing.js'])
    @endunless
</head>
<body class="gilas-landing">
    @include('components.footsteps')
    <main class="landing-stage" aria-label="خانه گیلاسی">
        <div class="landing-copy">
            <span class="landing-kicker">خانه گیلاسی</span>
            <h1>یک تجربه<br><strong>خوش‌طعم و متفاوت</strong></h1>
            <p>منو، فضا و حال‌وهوای خانه گیلاسی؛ همه‌چیز از یک لمس شروع می‌شود.</p>
        </div>
        <div class="honey-detail" data-honey-detail>
            <span class="honey-detail__eyebrow">انتخاب تو</span>
            <h2 data-honey-title>منوی خانه</h2>
            <p data-honey-text>از قهوه‌ی صبح تا دسرهای آخر شب؛ مسیرت را با منو شروع کن.</p>
            <button type="button" data-honey-action>مشاهده مسیر</button>
        </div>
        <div id="container" class="honeycomb" aria-label="شبکه تعاملی">
        @php
            $honeycomb = [5, 6, 7, 8, 9, 8, 7, 6, 5];
            $icons = ['🚀','🎸','🤖','🫶','🔥','🕹️','👾','✨','🌴','🖥️','💻','⌨️','💡','🕶️','⚙️','🍒','🧙‍♂️','🎮','👽','🌌','🎧','🌒','🌓','🌔','🎵','🎶','❤️','🎙️','📸','🕰️','🚀','🎸','🤖','🫶','🔥','🕹️','👾','✨','🌴','🖥️','💻','⌨️','💡','🕶️','⚙️','🍒','🦄','📱','🖨️','📡','🔬','🔭','🎚️','🎛️','🧬','🔮','🧲','🛸','🪐','🌠','👓'];
            $iconIndex = -1;
        @endphp
        @foreach ($honeycomb as $columnIndex => $column)
            <div class="honeycomb__column" style="--column: {{ $columnIndex }};">
                @for ($cellIndex = 1; $cellIndex <= $column; $cellIndex++)
                    @php $iconIndex++; @endphp
                    <button type="button" class="hexagon" style="--index: {{ $cellIndex }}; --icon: '{{ $icons[$iconIndex % count($icons)] }}';" aria-label="تعامل با خانه {{ $cellIndex }}"></button>
                @endfor
            </div>
        @endforeach
        </div>
        <button id="switch" class="vision-switch" type="button" aria-label="تغییر حالت نمایش" aria-pressed="false"><span aria-hidden="true"></span></button>
        <div class="landing-mark">G I L A S</div>
    </main>
</body>
</html>