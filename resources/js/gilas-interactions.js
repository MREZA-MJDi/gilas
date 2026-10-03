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

    const fallback = {
        title: 'گیلاس',
        text: 'مسیرت را از همین‌جا شروع کن.',
        cta: 'ورود به منو',
        url: '/menu',
        icon: '🍒',
    };

    let activeIndex = 0;
    let rippleRunning = false;

    const getItem = index => items[index % Math.max(items.length, 1)] || fallback;

    const activate = (index) => {
        activeIndex = index;

        hexagons.forEach((hex, i) => {
            const active = i === index;
            hex.classList.toggle('is-active', active);
            hex.setAttribute('aria-current', active ? 'true' : 'false');
        });

        const item = getItem(index);
        if (!detail || !title || !text || !action) return;

        title.textContent = item.title;
        text.textContent = item.text;
        action.textContent = item.cta;
        if (action instanceof HTMLAnchorElement) {
            action.href = item.url || fallback.url;
        }

        detail.classList.remove('is-visible');
        requestAnimationFrame(() => detail.classList.add('is-visible'));
    };

    const ripple = (target) => {
        if (rippleRunning) return;
        rippleRunning = true;

        const targetRect = target.getBoundingClientRect();
        const ordered = hexagons
            .map(element => {
                const rect = element.getBoundingClientRect();
                return {
                    element,
                    distance: Math.hypot(rect.x - targetRect.x, rect.y - targetRect.y),
                };
            })
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
        }, { once: true });
    };

    hexagons.forEach((hexagon, index) => {
        hexagon.addEventListener('click', () => {
            const item = getItem(index);

            activate(index);
            ripple(hexagon);

            if (item.url) {
                window.setTimeout(() => {
                    window.location.assign(item.url);
                }, reduceMotion ? 0 : 180);
            }
        });
        hexagon.addEventListener('mouseenter', () => activate(index));
        hexagon.addEventListener('focus', () => activate(index));
    });

    switchButton?.addEventListener('click', () => {
        const checked = !document.body.classList.contains('vision-ui');
        document.body.classList.toggle('vision-ui', checked);
        switchButton.classList.toggle('is-checked', checked);
        switchButton.setAttribute('aria-pressed', String(checked));
    });

    activate(activeIndex);

    if (!reduceMotion && hexagons[0]) {
        setTimeout(() => ripple(hexagons[0]), 500);
    }
}

initHoneycomb();
