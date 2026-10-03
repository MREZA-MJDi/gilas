import { gsap } from 'gsap';

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

    const modes = [
        ['منوی خانه', 'از قهوه‌ی صبح تا دسرهای آخر شب؛ مسیرت را با منو شروع کن.', 'مشاهده مسیر'],
        ['قهوه', 'عطر تازه، ریتم آرام و یک گوشه برای ماندن.', 'قهوه انتخاب کن'],
        ['دسر', 'چیزی شیرین برای پایان یک تجربه‌ی خوب.', 'دسرها'],
        ['فضای خانه', 'جایی برای مکث، گفت‌وگو و ساختن خاطره.', 'کشف فضا'],
        ['تجربه امروز', 'هر بار که برمی‌گردی، یک جزئیات تازه پیدا می‌کنی.', 'ادامه تجربه'],
        ['رزرو', 'برای میز بعدی‌ات از همینجا آماده شو.', 'رزرو میز'],
        ['درباره ما', 'خانه گیلاسی را از نگاه خودش بشناس.', 'داستان ما'],
        ['مسیریابی', 'راه رسیدن به یک توقف خوش‌مزه کوتاه است.', 'پیدا کردن ما'],
        ['باشگاه گیلاس', 'عضوی از جمعی باش که هر بار یک مزه‌ی تازه کشف می‌کند.', 'عضویت'],
    ];

    let rippleRunning = false;

    const activate = (index) => {
        hexagons.forEach((hex, i) => {
            const active = i === index;
            hex.classList.toggle('is-active', active);
            hex.setAttribute('aria-current', active ? 'true' : 'false');
        });

        if (!detail || !title || !text || !action) return;
        const [heading, copy, cta] = modes[index % modes.length];
        title.textContent = heading;
        text.textContent = copy;
        action.textContent = cta;
        detail.classList.remove('is-visible');
        requestAnimationFrame(() => detail.classList.add('is-visible'));
    };

    const ripple = (target) => {
        if (rippleRunning) return;
        rippleRunning = true;

        const targetRect = target.getBoundingClientRect();
        const ordered = hexagons.map(element => {
            const rect = element.getBoundingClientRect();
            return { element, distance: Math.hypot(rect.x - targetRect.x, rect.y - targetRect.y) };
        }).sort((a, b) => a.distance - b.distance);

        const maxDistance = ordered.at(-1)?.distance || 1;
        ordered.forEach(({ element, distance }) => {
            element.style.setProperty('--ripple-factor', String((distance * 100) / maxDistance));
        });

        container.classList.add('show-ripple');

        const last = ordered.at(-1)?.element;
        if (!last) return;

        last.addEventListener('animationend', () => {
            container.classList.remove('show-ripple');
            ordered.forEach(({ element }) => element.style.removeProperty('--ripple-factor'));
            rippleRunning = false;
        }, { once: true });
    };

    hexagons.forEach((hexagon, index) => {
        hexagon.addEventListener('click', () => {
            activate(index);
            ripple(hexagon);
        });
    });

    switchButton?.addEventListener('click', () => {
        const checked = !document.body.classList.contains('vision-ui');
        document.body.classList.toggle('vision-ui', checked);
        switchButton.classList.toggle('is-checked', checked);
        switchButton.setAttribute('aria-pressed', String(checked));
    });

    activate(0);
    if (!reduceMotion) {
        setTimeout(() => ripple(hexagons[0]), 500);
    }
}

initHoneycomb();
