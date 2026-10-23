(() => {
    const root = document.querySelector('[data-storefront]');
    if (!root) return;

    const hero = root.querySelector('[data-home-hero]');
    const slides = Array.from(root.querySelectorAll('[data-home-hero-slide]'));
    if (!hero || slides.length === 0) return;

    const image = root.querySelector('[data-home-hero-image]');
    const missing = root.querySelector('[data-home-hero-image-missing]');
    const price = root.querySelector('[data-home-hero-price]');
    const stock = root.querySelector('[data-home-hero-stock]');
    const caption = root.querySelector('[data-home-hero-caption]');
    const thumbnail = root.querySelector('[data-home-hero-thumbnail]');
    const thumbnailLink = root.querySelector('[data-home-hero-thumbnail-link]');
    const title = root.querySelector('[data-home-hero-title]');
    const description = root.querySelector('[data-home-hero-description]');
    const cta = root.querySelector('[data-home-hero-cta]');
    const featuredLink = root.querySelector('[data-home-hero-product-link]');
    const previous = root.querySelector('[data-home-hero-prev]');
    const next = root.querySelector('[data-home-hero-next]');
    const reduceMotion = window.matchMedia?.('(prefers-reduced-motion: reduce)').matches ?? false;
    let activeIndex = Math.max(0, slides.findIndex((slide) => slide.getAttribute('aria-pressed') === 'true'));
    let timer = null;

    function select(index) {
        activeIndex = (index + slides.length) % slides.length;
        const slide = slides[activeIndex];
        const src = slide.dataset.image || '';
        slides.forEach((node, i) => {
            const active = i === activeIndex;
            node.classList.toggle('is-selected', active);
            node.setAttribute('aria-pressed', active ? 'true' : 'false');
        });
        hero.style.setProperty('--home-hero-base', slide.dataset.theme || '#284a3d');
        hero.classList.remove('is-transitioning');
        window.requestAnimationFrame(() => hero.classList.add('is-transitioning'));

        if (title) title.textContent = slide.dataset.title || 'انتخاب منو';
        if (description) description.textContent = slide.dataset.description || '';
        if (price) price.textContent = slide.dataset.price || 'قیمت از منو';
        if (stock) stock.textContent = slide.dataset.stock || 'از منوی رستوران';
        if (caption) caption.textContent = slide.dataset.label || slide.dataset.title || 'منوی گیلاس';
        if (cta && slide.dataset.url) cta.href = slide.dataset.url;
        if (featuredLink && slide.dataset.url) featuredLink.href = slide.dataset.url;
        if (thumbnailLink && slide.dataset.url) thumbnailLink.href = slide.dataset.url;

        if (image) {
            image.hidden = !src;
            if (src && image.getAttribute('src') !== src) image.src = src;
            image.alt = slide.dataset.imageAlt || slide.dataset.title || 'غذای منوی گیلاس';
        }
        if (missing) missing.hidden = Boolean(src);
        if (thumbnail) {
            thumbnail.hidden = !src;
            if (src && thumbnail.getAttribute('src') !== src) thumbnail.src = src;
        }
        if (thumbnailLink) thumbnailLink.hidden = !src;
    }

    function restartTimer() {
        if (timer) window.clearInterval(timer);
        timer = null;
        if (slides.length < 2 || reduceMotion) return;
        timer = window.setInterval(() => select(activeIndex + 1), 6500);
    }

    slides.forEach((slide, index) => slide.addEventListener('click', () => {
        select(index);
        restartTimer();
    }));
    previous?.addEventListener('click', () => { select(activeIndex - 1); restartTimer(); });
    next?.addEventListener('click', () => { select(activeIndex + 1); restartTimer(); });
    hero.addEventListener('mouseenter', () => { if (timer) window.clearInterval(timer); });
    hero.addEventListener('mouseleave', restartTimer);
    if (previous) previous.disabled = slides.length < 2;
    if (next) next.disabled = slides.length < 2;
    select(activeIndex);
    restartTimer();
})();
