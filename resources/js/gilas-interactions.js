import { gsap } from 'gsap';

const reduceMotion = window.matchMedia?.('(prefers-reduced-motion: reduce)').matches ?? false;

function initLanding() {
    const branches = [...document.querySelectorAll('[data-tree-branch]')];
    const orbits = [...document.querySelectorAll('.tree-art__orbit')];
    const cicadas = [...document.querySelectorAll('.cicada')];
    const introItems = [...document.querySelectorAll('.landing-intro__eyebrow, .landing-intro h1, .landing-intro p, .landing-intro__cta')];
    const panel = document.querySelector('.category-panel');

    if (reduceMotion) {
        gsap.set(branches, { opacity: 0.82 });
        return;
    }

    branches.forEach((branch) => {
        const length = branch.getTotalLength?.() || 2000;
        gsap.set(branch, { strokeDasharray: length, strokeDashoffset: length });
    });

    const timeline = gsap.timeline({ defaults: { ease: 'power3.out' } });
    timeline.to(branches, { strokeDashoffset: 0, duration: 1.5, stagger: 0.07, ease: 'power2.inOut' }, 0.08);
    timeline.to(orbits, { scale: 1, opacity: 1, duration: 1, stagger: 0.12, ease: 'back.out(1.4)' }, 0.55);
    timeline.fromTo([...introItems, panel].filter(Boolean), { opacity: 0, y: 14 }, { opacity: 1, y: 0, duration: .65, stagger: .06 }, .65);

    if (orbits[0]) gsap.to(orbits[0], { rotation: 360, duration: 50, repeat: -1, ease: 'none', transformOrigin: 'center' });
    if (orbits[1]) gsap.to(orbits[1], { rotation: -360, duration: 34, repeat: -1, ease: 'none', transformOrigin: 'center' });

    cicadas.forEach((cicada, index) => {
        gsap.to(cicada, { y: index % 2 ? -6 : 6, x: index === 1 ? 4 : -3, duration: 2.8 + index * .35, repeat: -1, yoyo: true, ease: 'sine.inOut', delay: index * .2 });
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initLanding, { once: true });
} else {
    initLanding();
}
