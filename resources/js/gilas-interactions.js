import { gsap } from 'gsap';

const reduceMotion = window.matchMedia?.('(prefers-reduced-motion: reduce)').matches ?? false;

function initFootsteps() {
    const svg = document.querySelector('[data-footsteps]');
    if (!svg || reduceMotion) return;

    const stepCount = 9;
    const iconSize = 50;
    const repel = 35;
    const positions = [];
    const elements = [];
    const pointer = { x: 0, y: 0, dx: 0, dy: 0, angle: 0, moving: false, stopped: false };
    let steps = 0;
    let accumX = 0;
    let accumY = 0;
    let animating = false;

    const setViewport = () => svg.setAttribute('viewBox', `0 0 ${innerWidth} ${innerHeight}`);
    setViewport();
    addEventListener('resize', setViewport, { passive: true });

    for (let i = 0; i < stepCount; i++) {
        const el = document.createElementNS('http://www.w3.org/2000/svg', 'use');
        el.setAttribute('href', i % 2 ? '#gilas-feet-left' : '#gilas-feet-right');
        el.setAttribute('width', String(iconSize));
        el.setAttribute('height', String(iconSize));
        elements.push(el);
        positions.push({ x: -100, y: -100, angle: 0, age: 0 });
        svg.appendChild(el);
        gsap.set(el, { opacity: 0, transformOrigin: 'center center' });
    }

    const updateFoot = (el, index, left, delay = 0) => {
        const pos = positions[index];
        gsap.set(el, {
            delay,
            x: pos.x,
            y: pos.y,
            rotation: pos.angle,
            attr: { href: left ? '#gilas-feet-left' : '#gilas-feet-right' },
        });
    };

    const onPointerMove = (event) => {
        if (animating) return;

        if (pointer.x === 0 && pointer.y === 0) {
            pointer.x = event.clientX;
            pointer.y = event.clientY;
            return;
        }

        pointer.dx = event.clientX - pointer.x;
        pointer.dy = event.clientY - pointer.y;
        pointer.x = event.clientX;
        pointer.y = event.clientY;
        pointer.moving = true;
        accumX += pointer.dx;
        accumY += pointer.dy;

        const distance = Math.hypot(accumX, accumY);
        pointer.angle = Math.atan2(pointer.dx, pointer.dy);

        if (distance < 70) return;

        steps++;
        accumX = 0;
        accumY = 0;

        positions.unshift({
            x: pointer.x - Math.sin(pointer.angle) * repel,
            y: pointer.y - Math.cos(pointer.angle) * repel,
            angle: (1 - pointer.angle / Math.PI) * 180,
            age: 1,
        });
        positions.length = stepCount;

        for (let i = 1; i < stepCount; i++) {
            const left = i % 2 === steps % 2;
            updateFoot(elements[i], i, left);
        }
        gsap.set(elements[0], { opacity: 0 });
    };

    addEventListener('pointermove', onPointerMove, { passive: true });

    const render = () => {
        const fade = pointer.moving ? 0.05 : 0.1;

        for (let i = 1; i < stepCount; i++) {
            positions[i].age = Math.max(0, positions[i].age - fade);
        }
        for (let i = 2; i < stepCount; i++) {
            gsap.set(elements[i], { opacity: positions[i].age });
        }

        if (pointer.moving) {
            pointer.moving = false;
            pointer.stopped = true;
        } else if (pointer.stopped) {
            pointer.stopped = false;

            updateFoot(elements[0], 0, steps % 2 === 0);
            gsap.set(elements[0], { opacity: 1 });

            updateFoot(elements[1], 0, steps % 2 === 1, 0.1);
            gsap.set(elements[1], { opacity: 1, delay: 0.1 });

            for (let i = 2; i < stepCount; i++) {
                updateFoot(elements[i], i - 1, (i - 1) % 2 === steps % 2);
            }
        }

        requestAnimationFrame(render);
    };

    render();

    if (document.body.classList.contains('gilas-landing')) {
        animating = true;
        const coords = { x: -100, y: innerHeight };
        gsap.timeline({
            onUpdate: () => onPointerMove({ clientX: coords.x, clientY: coords.y }),
            onComplete: () => { animating = false; },
        })
            .to(coords, { x: innerWidth * 0.4, duration: 1.5, ease: 'power1.out' })
            .to(coords, { y: innerHeight * 0.6, duration: 1.1, ease: 'back.out(2.5)' }, 0);
    }
}

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

initFootsteps();
initHoneycomb();
