import './bootstrap';
import { gsap } from 'gsap';

const reduceMotion = window.matchMedia?.('(prefers-reduced-motion: reduce)').matches ?? false;

function initHome() {
    const home = document.querySelector('[data-home]');
    if (!home) return;

    const heroImage = home.querySelector('[data-home-hero-image]');
    if (!reduceMotion && heroImage) {
        gsap.fromTo(heroImage, { scale: 1.08 }, { scale: 1.03, duration: 1.7, ease: 'power3.out' });
    }

    const reveals = [...home.querySelectorAll('[data-home-reveal]')];
    if (reduceMotion) {
        reveals.forEach((el) => el.classList.add('is-revealed'));
    } else {
        gsap.to(reveals, {
            opacity: 1,
            y: 0,
            duration: .8,
            stagger: .07,
            ease: 'power3.out',
            clearProps: 'transform',
        });
    }

    const track = home.querySelector('[data-home-slider-track]');
    const prev = home.querySelector('[data-home-slider-prev]');
    const next = home.querySelector('[data-home-slider-next]');
    const step = () => track?.querySelector('.home-slider__item')?.getBoundingClientRect().width ? track.querySelector('.home-slider__item').getBoundingClientRect().width + 16 : 300;

    prev?.addEventListener('click', () => track?.scrollBy({ left: -step(), behavior: reduceMotion ? 'auto' : 'smooth' }));
    next?.addEventListener('click', () => track?.scrollBy({ left: step(), behavior: reduceMotion ? 'auto' : 'smooth' }));

    if (!reduceMotion && heroImage) {
        home.querySelector('[data-home-hero]')?.addEventListener('pointermove', (event) => {
            const hero = event.currentTarget.getBoundingClientRect();
            const nx = (event.clientX - hero.left) / hero.width - .5;
            const ny = (event.clientY - hero.top) / hero.height - .5;
            gsap.to(heroImage, { x: nx * 10, y: ny * 6, duration: .45, ease: 'power2.out', overwrite: true });
        });
        home.querySelector('[data-home-hero]')?.addEventListener('pointerleave', () => {
            gsap.to(heroImage, { x: 0, y: 0, duration: .55, ease: 'power2.out' });
        });
    }
}
document.addEventListener('DOMContentLoaded', initHome);
