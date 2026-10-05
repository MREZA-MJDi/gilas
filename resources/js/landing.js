import './bootstrap';
import { gsap } from 'gsap';

const reduceMotion = window.matchMedia?.('(prefers-reduced-motion: reduce)').matches ?? false;

function initHome() {
    const home = document.querySelector('[data-home]');
    if (!home) return;

    const view = home.querySelector('.hero-grid-view');
    const viewImg = view?.querySelector('.img');
    const viewName = view?.querySelector('[data-view-name]');
    const viewPrice = view?.querySelector('[data-view-price]');
    const items = [...home.querySelectorAll('.home-hero-gallery .pic')];
    const prev = view?.querySelector('.arrow--prev');
    const next = view?.querySelector('.arrow--next');

    const position = {
        current: 0,
        max: Math.max(0, items.length - 1),
    };

    const formatPrice = (value) => {
        const amount = Number.parseInt(value || '0', 10);
        if (!Number.isFinite(amount) || amount <= 0) return '';
        const number = new Intl.NumberFormat('fa-IR').format(amount);
        const currency = home.dataset.currency || '';
        return number + (currency ? ' ' + currency : '');
    };

    const updateFocusableItems = () => {
        items.forEach((item) => {
            const visible = Number.parseFloat(getComputedStyle(item).opacity) > 0.5;
            item.tabIndex = visible ? 0 : -1;
            item.setAttribute('aria-hidden', visible ? 'false' : 'true');
        });
    };

    const updateOrientation = () => {
        if (!view || !viewImg || !viewImg.naturalWidth || !viewImg.naturalHeight) return;
        view.dataset.isVertical = viewImg.naturalHeight >= viewImg.naturalWidth ? '1' : '0';
    };

    const changeImage = () => {
        if (!view || !viewImg || !items[position.current]) return;

        const item = items[position.current];
        const src = item.dataset.image || '';

        viewImg.alt = item.dataset.name || 'تصویر محصول';
        viewImg.onload = updateOrientation;
        viewImg.src = src;

        if (viewName) {
            viewName.textContent = item.dataset.name || '';
        }

        if (viewPrice) {
            viewPrice.textContent = formatPrice(item.dataset.price);
        }

        requestAnimationFrame(updateOrientation);
    };

    const showView = (index) => {
        if (!view || !items[index]) return;

        position.current = Math.max(0, Math.min(index, position.max));
        view.dataset.state = 'open';
        view.setAttribute('aria-hidden', 'false');
        document.documentElement.style.overflow = 'hidden';
        changeImage();
    };

    const hideView = () => {
        if (!view || !viewImg) return;

        view.dataset.state = 'closed';
        view.setAttribute('aria-hidden', 'true');
        viewImg.src = '';
        viewImg.alt = '';
        if (viewName) viewName.textContent = '';
        if (viewPrice) viewPrice.textContent = '';
        document.documentElement.style.overflow = '';
    };

    const showPrev = (event) => {
        event?.stopPropagation();

        position.current = position.current === 0
            ? position.max
            : position.current - 1;

        changeImage();
    };

    const showNext = (event) => {
        event?.stopPropagation();

        position.current = position.current === position.max
            ? 0
            : position.current + 1;

        changeImage();
    };

    items.forEach((item, index) => {
        item.dataset.position = String(index);
        item.addEventListener('click', () => showView(index));

        item.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                showView(index);
            }
        });
    });

    view?.addEventListener('click', hideView);
    prev?.addEventListener('click', showPrev);
    next?.addEventListener('click', showNext);

    window.addEventListener('resize', updateFocusableItems);

    window.addEventListener('keyup', (event) => {
        if (view?.dataset.state !== 'open') return;

        if (event.key === 'Escape') {
            hideView();
        } else if (event.key === 'ArrowLeft') {
            showPrev();
        } else if (event.key === 'ArrowRight' || event.key === ' ') {
            event.preventDefault();
            showNext();
        }
    });

    updateFocusableItems();

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

        if (items.length) {
            gsap.fromTo(items, {
                scale: .96,
                y: 18,
            }, {
                scale: 1,
                y: 0,
                duration: .65,
                stagger: .045,
                ease: 'power3.out',
                clearProps: 'transform',
            });
        }
    }
}

document.addEventListener('DOMContentLoaded', initHome);
