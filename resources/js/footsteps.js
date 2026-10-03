import { gsap } from 'gsap';

const reduceMotion = window.matchMedia?.('(prefers-reduced-motion: reduce)').matches ?? false;

export function initFootsteps() {
    const svg = document.querySelector('[data-footsteps]');
    if (!svg || reduceMotion || document.body.hasAttribute('data-disable-footsteps')) return;

    const count = 10;
    const footprintSize = 48;
    const mouseSpacing = 64;
    const touchSpacing = 44;
    const elements = [];
    const state = {
        activePointerId: null,
        lastStepX: null,
        lastStepY: null,
        direction: 1,
    };

    const createFoot = (left) => {
        const element = document.createElementNS('http://www.w3.org/2000/svg', 'use');
        element.setAttribute('href', left ? '#gilas-feet-left' : '#gilas-feet-right');
        element.setAttribute('width', String(footprintSize));
        element.setAttribute('height', String(footprintSize));
        svg.appendChild(element);
        gsap.set(element, { opacity: 0, transformOrigin: 'center center' });
        return element;
    };

    for (let i = 0; i < count; i++) elements.push(createFoot(i % 2 === 0));

    const place = (x, y, angle, immediate = false) => {
        const element = elements.shift();
        if (!element) return;

        elements.push(element);
        state.direction *= -1;

        gsap.killTweensOf(element);
        gsap.set(element, {
            x,
            y,
            rotation: angle,
            attr: { href: state.direction > 0 ? '#gilas-feet-left' : '#gilas-feet-right' },
            opacity: immediate ? .72 : .6,
        });
        gsap.to(element, {
            opacity: 0,
            duration: .8,
            delay: immediate ? .42 : .10,
            ease: 'power2.out',
            overwrite: 'auto',
        });

        state.lastStepX = x;
        state.lastStepY = y;
    };

    const onPointerDown = (event) => {
        if (!event.isPrimary || (event.pointerType === 'mouse' && event.button !== 0)) return;

        state.activePointerId = event.pointerId;
        state.lastStepX = event.clientX;
        state.lastStepY = event.clientY;

        place(event.clientX - footprintSize / 2, event.clientY - footprintSize / 2, 0, true);
    };

    const onPointerMove = (event) => {
        if (!event.isPrimary || state.activePointerId !== event.pointerId) return;

        const dx = event.clientX - state.lastStepX;
        const dy = event.clientY - state.lastStepY;
        const spacing = event.pointerType === 'touch' || event.pointerType === 'pen' ? touchSpacing : mouseSpacing;

        if (Math.hypot(dx, dy) < spacing) return;

        place(
            event.clientX - footprintSize / 2,
            event.clientY - footprintSize / 2,
            Math.atan2(dy, dx) * (180 / Math.PI) + 90
        );
    };

    const reset = (event) => {
        if (event.pointerId !== state.activePointerId) return;
        state.activePointerId = null;
        state.lastStepX = null;
        state.lastStepY = null;
    };

    addEventListener('pointerdown', onPointerDown, { passive: true });
    addEventListener('pointermove', onPointerMove, { passive: true });
    addEventListener('pointerup', reset, { passive: true });
    addEventListener('pointercancel', reset, { passive: true });
}

initFootsteps();
