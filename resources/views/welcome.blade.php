<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="description" content="۲۰۲۵ — صفحه‌ی اصلی">
    <title>۲۰۲۵ — Cicada Tree</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Estedad:wght@300;400;500;600;700&family=Vazirmatn:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/gilas-coming.css') }}">
</head>
<body>
    <div class="ambient" aria-hidden="true">
        <div class="ambient__spot" data-spot></div>
        <div class="ambient__beam" data-beam>
            <div class="ambient__beam-core">
                <canvas data-dust></canvas>
            </div>
        </div>
    </div>

    <header class="mini-nav" aria-label="ناوبری اصلی">
        <span class="mini-nav__year">۲۰۲۵</span>
    </header>

    <main id="main" data-page-id="home">
        <section class="tree-stage" aria-label="Cicada Tree">
            <div class="tree-stage__video" aria-hidden="true">
                <video autoplay muted loop playsinline>
                    <source src="https://cdn.zajno.com/dev/codepen/cicada/cicada_tree.webm" type="video/webm">
                    <source src="https://cdn.zajno.com/dev/codepen/cicada/cicada_tree.mov" type='video/mp4; codecs="hvc1"'>
                </video>
            </div>

            <div class="tree" data-tree>
                <div class="tree-name" aria-hidden="true"></div>

                <div class="tree-wrapp">
                    <svg class="tree-svg" viewBox="0 0 2556 3440" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path class="tree-svg__bottom" d="M1278.07 3439.61L1278.07 1717" stroke="var(--tree-line)" stroke-width="3.21"/>
                        <path class="tree-svg__top tree-svg__branches" data-position="top" d="M1278.07 1722.61L1278.07 0" stroke="var(--tree-line)" stroke-width="3.21"/>
                        <path class="tree-svg__left tree-svg__branches" data-position="left" d="M1278.07 1716.2L2 651.193" stroke="var(--tree-line)" stroke-width="3.21"/>
                        <path class="tree-svg__left-top tree-svg__branches" data-position="left-top" d="M1278.07 1716.2L552.609 176.432" stroke="var(--tree-line)" stroke-width="3.21"/>
                        <path class="tree-svg__right-top tree-svg__branches" data-position="right-top" d="M1278.08 1716.2L2002.43 176.432" stroke="var(--tree-line)" stroke-width="3.21"/>
                        <path class="tree-svg__right tree-svg__branches" data-position="right" d="M1278.07 1716.2L2554.09 651.193" stroke="var(--tree-line)" stroke-width="3.21"/>
                    </svg>

                    <div class="tree-circle tree-circle__big"></div>
                    <div class="tree-circle tree-circle__small"></div>

                    <div class="tree-title tree-title_left" data-position="left" aria-hidden="true">
                        <svg class="tree-title__decor left" viewBox="0 0 10 3" fill="none"><path d="M10 1H3C1.89543 1 1 1.89543 1 3V3" stroke="var(--tree-line)"/></svg>
                        <span></span>
                        <svg class="tree-title__decor right" viewBox="0 0 10 3" fill="none"><path d="M0 1H7C8.10457 1 9 1.89543 9 3V3" stroke="var(--tree-line)"/></svg>
                    </div>
                    <div class="tree-title tree-title_left-top" data-position="left-top" aria-hidden="true">
                        <svg class="tree-title__decor left" viewBox="0 0 10 3" fill="none"><path d="M10 1H3C1.89543 1 1 1.89543 1 3V3" stroke="var(--tree-line)"/></svg>
                        <span></span>
                        <svg class="tree-title__decor right" viewBox="0 0 10 3" fill="none"><path d="M0 1H7C8.10457 1 9 1.89543 9 3V3" stroke="var(--tree-line)"/></svg>
                    </div>
                    <div class="tree-title tree-title_top" data-position="top" aria-hidden="true">
                        <svg class="tree-title__decor left" viewBox="0 0 10 3" fill="none"><path d="M10 1H3C1.89543 1 1 1.89543 1 3V3" stroke="var(--tree-line)"/></svg>
                        <span></span>
                        <svg class="tree-title__decor right" viewBox="0 0 10 3" fill="none"><path d="M0 1H7C8.10457 1 9 1.89543 9 3V3" stroke="var(--tree-line)"/></svg>
                    </div>
                    <div class="tree-title tree-title_right-top" data-position="right-top" aria-hidden="true">
                        <svg class="tree-title__decor left" viewBox="0 0 10 3" fill="none"><path d="M10 1H3C1.89543 1 1 1.89543 1 3V3" stroke="var(--tree-line)"/></svg>
                        <span></span>
                        <svg class="tree-title__decor right" viewBox="0 0 10 3" fill="none"><path d="M0 1H7C8.10457 1 9 1.89543 9 3V3" stroke="var(--tree-line)"/></svg>
                    </div>
                    <div class="tree-title tree-title_right" data-position="right" aria-hidden="true">
                        <svg class="tree-title__decor left" viewBox="0 0 10 3" fill="none"><path d="M10 1H3C1.89543 1 1 1.89543 1 3V3" stroke="var(--tree-line)"/></svg>
                        <span></span>
                        <svg class="tree-title__decor right" viewBox="0 0 10 3" fill="none"><path d="M0 1H7C8.10457 1 9 1.89543 9 3V3" stroke="var(--tree-line)"/></svg>
                    </div>

                    <div class="tree-ball tree-ball_left" data-position="left"><span class="tree-ball__1"></span><span class="tree-ball__2"></span><span class="tree-ball__3"></span></div>
                    <div class="tree-ball tree-ball_left-top" data-position="left-top"><span class="tree-ball__1"></span><span class="tree-ball__2"></span><span class="tree-ball__3"></span></div>
                    <div class="tree-ball tree-ball_top" data-position="top"><span class="tree-ball__1"></span><span class="tree-ball__2"></span><span class="tree-ball__3"></span></div>
                    <div class="tree-ball tree-ball_right-top" data-position="right-top"><span class="tree-ball__1"></span><span class="tree-ball__2"></span><span class="tree-ball__3"></span></div>
                    <div class="tree-ball tree-ball_right" data-position="right"><span class="tree-ball__1"></span><span class="tree-ball__2"></span><span class="tree-ball__3"></span></div>
                </div>
            </div>

            <div class="year-caption" aria-hidden="true">۲۰۲۵</div>
        </section>

        <div class="leader-block" data-leader data-open="{{ now()->addDays(10)->toIso8601String() }}" aria-label="شمارش معکوس تا افتتاح">
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
    </main>

    <canvas class="grain" data-grain aria-hidden="true"></canvas>

    <script src="https://unpkg.co/gsap@3/dist/gsap.min.js"></script>
    <script src="https://unpkg.com/gsap@3/dist/ScrollTrigger.min.js"></script>
    <script src="https://assets.codepen.io/16327/DrawSVGPlugin3.min.js"></script>
    <script src="https://unpkg.com/gsap@3/dist/MotionPathPlugin.min.js"></script>
    <script src="https://assets.codepen.io/16327/CustomEase3.min.js"></script>
    <script src="{{ asset('js/gilas-coming.js') }}"></script>
</body>
</html>
