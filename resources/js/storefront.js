(() => {
    const root = document.querySelector('[data-storefront]');
    if (!root) return;

    const hero = root.querySelector('[data-gilas-hero]');
    const reduceMotion = window.matchMedia?.('(prefers-reduced-motion: reduce)').matches ?? false;

    if (hero) {
        const slides = Array.from(hero.querySelectorAll('[data-hero-slide]'));
        const previousButton = hero.querySelector('[data-hero-prev]');
        const nextButton = hero.querySelector('[data-hero-next]');
        const dots = Array.from(hero.querySelectorAll('[data-hero-dot]'));
        const progress = hero.querySelector('[data-hero-progress]');
        const delay = 6800;
        let activeIndex = Math.max(0, slides.findIndex((slide) => slide.classList.contains('is-active')));
        let autoplayTimer = null;
        let startedAt = 0;
        let remaining = delay;
        let transitionLock = false;
        let transitionTimer = null;
        let touchStartX = null;
        let paused = false;

        const setActiveState = (index) => {
            activeIndex = (index + slides.length) % slides.length;

            slides.forEach((slide, slideIndex) => {
                const active = slideIndex === activeIndex;
                slide.classList.toggle('is-active', active);
                slide.setAttribute('aria-hidden', String(!active));
                slide.inert = !active;
            });

            dots.forEach((dot, dotIndex) => {
                const active = dotIndex === activeIndex;
                dot.classList.toggle('is-active', active);
                dot.setAttribute('aria-selected', String(active));
                dot.tabIndex = active ? 0 : -1;
            });
        };

        const resetProgress = (duration) => {
            if (!progress) return;
            progress.style.setProperty('--gilas-hero-duration', String(duration) + 'ms');
            progress.classList.remove('is-running', 'is-paused');
            void progress.offsetWidth;
            if (!reduceMotion) requestAnimationFrame(() => progress.classList.add('is-running'));
        };

        const stopAutoplay = () => {
            if (autoplayTimer !== null) {
                window.clearTimeout(autoplayTimer);
                autoplayTimer = null;
                remaining = Math.max(0, remaining - (performance.now() - startedAt));
            }
            progress?.classList.add('is-paused');
            paused = true;
        };

        const scheduleAutoplay = (duration = delay) => {
            if (reduceMotion || slides.length < 2 || document.hidden) return;
            if (autoplayTimer !== null) window.clearTimeout(autoplayTimer);
            remaining = duration;
            startedAt = performance.now();
            paused = false;
            resetProgress(duration);
            autoplayTimer = window.setTimeout(() => {
                autoplayTimer = null;
                remaining = delay;
                showSlide(activeIndex + 1);
            }, duration);
        };

        const showSlide = (index) => {
            if (slides.length < 2 || transitionLock) return;
            transitionLock = true;
            setActiveState(index);
            remaining = delay;

            if (transitionTimer !== null) window.clearTimeout(transitionTimer);
            transitionTimer = window.setTimeout(() => {
                transitionLock = false;
                transitionTimer = null;
            }, reduceMotion ? 20 : 980);

            if (autoplayTimer !== null) {
                window.clearTimeout(autoplayTimer);
                autoplayTimer = null;
            }
            if (!paused && !reduceMotion) scheduleAutoplay(delay);
            else resetProgress(delay);
        };

        previousButton?.addEventListener('click', () => showSlide(activeIndex - 1));
        nextButton?.addEventListener('click', () => showSlide(activeIndex + 1));
        dots.forEach((dot, index) => dot.addEventListener('click', () => showSlide(index)));

        hero.addEventListener('mouseenter', () => {
            if (!reduceMotion && slides.length > 1 && !paused) stopAutoplay();
        });
        hero.addEventListener('mouseleave', () => {
            if (paused && !hero.contains(document.activeElement) && !document.hidden) scheduleAutoplay(remaining || delay);
        });
        hero.addEventListener('focusin', () => {
            if (!reduceMotion && slides.length > 1 && !paused) stopAutoplay();
        });
        hero.addEventListener('focusout', (event) => {
            if (paused && !hero.contains(event.relatedTarget) && !document.hidden) scheduleAutoplay(remaining || delay);
        });
        hero.addEventListener('keydown', (event) => {
            if (event.altKey || event.ctrlKey || event.metaKey || event.target.matches('a,button,input,textarea,select')) return;
            if (event.key === 'ArrowLeft') {
                event.preventDefault();
                showSlide(activeIndex + 1);
            } else if (event.key === 'ArrowRight') {
                event.preventDefault();
                showSlide(activeIndex - 1);
            }
        });
        hero.addEventListener('pointerdown', (event) => {
            if (event.pointerType !== 'touch' || event.target.closest('a,button')) return;
            touchStartX = event.clientX;
        }, { passive: true });
        hero.addEventListener('pointerup', (event) => {
            if (touchStartX === null) return;
            const deltaX = event.clientX - touchStartX;
            touchStartX = null;
            if (Math.abs(deltaX) > 48) showSlide(deltaX < 0 ? activeIndex + 1 : activeIndex - 1);
        }, { passive: true });
        hero.addEventListener('pointercancel', () => { touchStartX = null; }, { passive: true });
        document.addEventListener('visibilitychange', () => {
            if (document.hidden && !paused) stopAutoplay();
            else if (!document.hidden && paused && !hero.matches(':hover') && !hero.contains(document.activeElement)) {
                scheduleAutoplay(remaining || delay);
            }
        });

        if (slides.length > 1) {
            setActiveState(activeIndex);
            if (!reduceMotion) scheduleAutoplay(delay);
        } else if (slides.length === 1) {
            setActiveState(0);
            if (progress) progress.style.transform = 'scaleX(1)';
            previousButton?.setAttribute('hidden', '');
            nextButton?.setAttribute('hidden', '');
        }
    }

    const revealTargets = Array.from(root.querySelectorAll('[data-gilas-reveal]'));
    if (revealTargets.length && !reduceMotion && 'IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries, currentObserver) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('is-revealed');
                entry.target.classList.remove('is-reveal-pending');
                currentObserver.unobserve(entry.target);
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -4% 0px' });

        revealTargets.forEach((target, index) => {
            target.style.transitionDelay = String(Math.min(index % 5, 4) * 45) + 'ms';
            target.classList.add('is-reveal-pending');
            observer.observe(target);
        });
    } else {
        revealTargets.forEach((target) => target.classList.add('is-revealed'));
    }
})();
