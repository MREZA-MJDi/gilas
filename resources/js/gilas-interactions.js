const reduceMotion = window.matchMedia?.('(prefers-reduced-motion: reduce)').matches ?? false;

function initHoneycomb() {
    const container = document.getElementById('container');
    if (!container) return;

    const hexagons = [...container.querySelectorAll('.hexagon')];
    const switchButton = document.getElementById('switch');
    const detail = document.querySelector('[data-honey-detail]');
    const title = detail?.querySelector('[data-honey-title]');
    const text = detail?.querySelector('[data-honey-text]');
    const action = detail?.querySelector('[data-honey-action]');
    if (!hexagons.length) return;

    let items = [];
    try {
        items = JSON.parse(container.dataset.honeycomb || '[]');
    } catch {
        items = [];
    }

    const categories = items.filter(item => item.type === 'category');
    const guides = items.filter(item => item.type === 'guide');
    let activeIndex = 0;
    let rippleRunning = false;
    let guideTimer = null;

    const fallback = {
        title:'گیلاس',
        text:'این سلول بخشی از ریتم تعاملی Landing است.',
        cta:'راهنما',
        url:'/menu',
        type:'decorative',
    };

    const itemFor = (hex) => {
        const type = hex.dataset.honeyType;
        const slug = hex.dataset.honeyTarget;

        if (type === 'category') {
            return categories.find(item => item.slug === slug) || fallback;
        }

        if (type === 'guide') {
            const title = hex.getAttribute('aria-label');
            return guides.find(item => item.title === title) || guides[0] || fallback;
        }

        return fallback;
    };

    const setDetail = (item) => {
        if (!detail || !title || !text || !action) return;

        title.textContent = item.title;
        text.textContent = item.text;
        action.textContent = item.cta || 'شروع';
        action.href = item.url || '#';
        detail.classList.remove('is-visible');
        requestAnimationFrame(() => detail.classList.add('is-visible'));
    };

    const activate = (index, item = itemFor(hexagons[index])) => {
        activeIndex = index;
        hexagons.forEach((hex, i) => hex.classList.toggle('is-active', i === index));
        setDetail(item);
    };

    const ripple = (target) => {
        if (rippleRunning || reduceMotion) return;

        rippleRunning = true;
        const targetIndex = Number(target.dataset.honeyIndex || 0);

        const ordered = hexagons
            .map((element, index) => ({
                element,
                distance: Math.abs(index - targetIndex),
            }))
            .sort((a, b) => a.distance - b.distance);

        const maxDistance = ordered.at(-1)?.distance || 1;

        ordered.forEach(({ element, distance }) => {
            element.style.setProperty('--ripple-factor', String((distance * 100) / maxDistance));
        });

        container.classList.add('show-ripple');

        const last = ordered.at(-1)?.element;
        if (!last) {
            rippleRunning = false;
            container.classList.remove('show-ripple');
            return;
        }

        last.addEventListener('animationend', () => {
            container.classList.remove('show-ripple');
            ordered.forEach(({ element }) => element.style.removeProperty('--ripple-factor'));
            rippleRunning = false;
        }, { once:true });
    };

    const runGuide = (startHex) => {
        if (guideTimer) window.clearTimeout(guideTimer);

        const guideHexes = hexagons.filter(hex => hex.dataset.honeyType === 'guide');
        const startTitle = startHex?.getAttribute('aria-label');
        let step = Math.max(0, guides.findIndex(item => item.title === startTitle));

        const play = () => {
            const current = guides[step % Math.max(guides.length, 1)] || fallback;
            const target = guideHexes[step % Math.max(guideHexes.length, 1)] || startHex;

            if (!target) return;

            hexagons.forEach(hex => hex.classList.remove('is-active'));
            target.classList.add('is-active');
            setDetail(current);
            ripple(target);

            if (step >= guides.length - 1) {
                guideTimer = window.setTimeout(() => {
                    hexagons.forEach(hex => hex.classList.remove('is-active'));
                    activate(0);
                }, reduceMotion ? 800 : 2200);
                return;
            }

            step += 1;
            guideTimer = window.setTimeout(play, reduceMotion ? 700 : 1500);
        };

        play();
    };

    hexagons.forEach((hexagon, index) => {
        hexagon.addEventListener('click', () => {
            const item = itemFor(hexagon);

            if (hexagon.dataset.honeyType === 'guide') {
                runGuide(hexagon);
                return;
            }

            if (hexagon.dataset.honeyType === 'decorative') {
                activate(index, {
                    title:'یک لحظه مکث',
                    text:'همه سلول‌ها لینک نیستند؛ بعضی‌ها برای حرکت، ریتم و حس خود Landing هستند.',
                    cta:'راهنما',
                    type:'guide',
                });
                ripple(hexagon);
                return;
            }

            activate(index, item);
            ripple(hexagon);

            if (item.url) {
                window.setTimeout(() => window.location.assign(item.url), reduceMotion ? 0 : 220);
            }
        });

        hexagon.addEventListener('mouseenter', () => {
            if (hexagon.dataset.honeyType !== 'decorative') activate(index);
        });

        hexagon.addEventListener('focus', () => {
            if (hexagon.dataset.honeyType !== 'decorative') activate(index);
        });
    });

    action?.addEventListener('click', event => {
        if (action.getAttribute('href') !== '#') return;
        event.preventDefault();
        runGuide(hexagons[activeIndex]);
    });

    switchButton?.addEventListener('click', () => {
        const checked = !document.body.classList.contains('vision-ui');
        document.body.classList.toggle('vision-ui', checked);
        switchButton.classList.toggle('is-checked', checked);
        switchButton.setAttribute('aria-pressed', String(checked));
    });

    activate(activeIndex);

    if (!reduceMotion && hexagons[0]) {
        window.setTimeout(() => ripple(hexagons[0]), 450);
    }
}

initHoneycomb();
