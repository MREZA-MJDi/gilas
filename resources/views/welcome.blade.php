<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="یک فضای تازه برای قهوه، دسر، گفت‌وگو و مکث؛ به‌زودی.">
    <title>گلاس — به‌زودی</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Estedad:wght@400;500;600;700;800&family=Vazirmatn:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/gilas-coming.css') }}">
</head>
<body>
    <div class="house" aria-hidden="true">
        <div class="spot" data-spot></div>
        <div class="beam-pivot" data-beam>
            <div class="beam">
                <canvas class="beam-dust" data-dust></canvas>
            </div>
        </div>
    </div>

    <header class="top">
        <p class="wordmark">گلاس<span class="wordmark-lamp" aria-hidden="true"></span></p>
        <p class="top-place">یک مکث تازه</p>
    </header>

    <main class="stage">
        <div class="leader-block" data-leader data-open="{{ now()->addDays(10)->toIso8601String() }}" aria-label="شمارش تا افتتاح">
            <div class="leader">
                <span class="leader-sweep" data-leader-sweep></span>
                <span class="leader-ring"></span>
                <span class="leader-cross"></span>
                <span class="leader-num" data-leader-num></span>
            </div>
            <p class="leader-cap">
                <span class="leader-cap-key" data-leader-key></span>
                <span class="leader-cap-sub" data-leader-sub></span>
            </p>
        </div>

        <section class="slide" data-slide>
            <p class="kicker">به‌زودی</p>

            <h1 class="title" aria-label="چراغ‌ها آرام می‌شوند، یک مکث تازه از راه می‌رسد">
                <span class="line"><span class="line-inner">چراغ‌ها آرام می‌شوند</span></span>
                <span class="line"><span class="line-inner">یک مکث تازه</span></span>
                <span class="line"><span class="line-inner">از راه می‌رسد.</span></span>
            </h1>

            <div class="rule" aria-hidden="true"><span class="rule-bar"></span></div>

            <p class="sub">
                جایی برای قهوه‌ای آرام، دسرهای دست‌ساز، گفت‌وگوهای طولانی و
                چند دقیقه فاصله گرفتن از شتاب روزمره؛ فضایی گرم که قرار است
                خیلی زود در را به روی شما باز کند.
            </p>

            <ul class="facts">
                <li class="fact"><span class="fact-key">تا افتتاح</span><span class="fact-val">۱۰ روز</span></li>
                <li class="fact"><span class="fact-key">حال‌و‌هوا</span><span class="fact-val">آرام و صمیمی</span></li>
                <li class="fact"><span class="fact-key">تجربه</span><span class="fact-val">قهوه، دسر و مکث</span></li>
            </ul>

            <form class="notify" action="#" method="post" data-notify>
                <div class="notify-field">
                    <label class="sr-only" for="email">نشانی ایمیل</label>
                    <input class="notify-input" id="email" type="email" name="email" placeholder="ایمیل شما" required autocomplete="email">
                    <button class="notify-btn" type="submit">خبرم کن</button>
                </div>
                <p class="notify-done" hidden>ثبت شد؛ زمان افتتاح را برایتان می‌فرستیم.</p>
                <p class="notify-status sr-only" role="status" aria-live="polite"></p>
                <p class="notify-note">فقط یک پیام در زمان افتتاح؛ همین.</p>
            </form>
        </section>
    </main>

    <footer class="foot">
        <div class="foot-links">
            <span class="foot-link">به‌زودی بیشتر می‌گوییم</span>
        </div>
        <p class="foot-note">درِ این فضا به‌زودی باز می‌شود.</p>
    </footer>

    <canvas class="grain" data-grain aria-hidden="true"></canvas>

    <script src="https://cdn.jsdelivr.net/npm/gsap@3.15.0/dist/gsap.min.js"></script>
    <script src="{{ asset('js/gilas-coming.js') }}"></script>
</body>
</html>
