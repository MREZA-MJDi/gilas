(() => {
    const root = document.querySelector('[data-public-search]')?.closest('#public-menu');
    if (!root) return;

    const search = root.querySelector('[data-public-search]');
    const searchStatus = root.querySelector('[data-search-status]');
    const searchEmpty = root.querySelector('[data-search-empty]');
    const clearButton = root.querySelector('[data-clear-search]');
    const filters = [...root.querySelectorAll('[data-public-category]')];
    const sections = [...root.querySelectorAll('[data-public-section]')];
    const hasFullMenu = root.dataset.fullMenu === '1';
    const reducedMotion = window.matchMedia?.('(prefers-reduced-motion: reduce)').matches ?? false;

    const normalize = value => String(value || '')
        .trim()
        .toLocaleLowerCase('fa-IR')
        .replace(/[يى]/g, 'ی')
        .replace(/ك/g, 'ک');

    const apply = () => {
        const query = normalize(search?.value);
        let visible = 0;

        sections.forEach(section => {
            let sectionVisible = 0;

            section.querySelectorAll('[data-public-card]').forEach(card => {
                const haystack = normalize(card.dataset.search);
                const match = !query || haystack.includes(query);
                card.hidden = !match;
                if (match) sectionVisible++;
            });

            section.hidden = sectionVisible === 0;
            const count = section.querySelector('[data-category-count]');
            if (count) {
                count.textContent = toFa(sectionVisible) + ' انتخاب';
            }
            visible += sectionVisible;
        });

        filters.forEach(filter => {
            const slug = filter.dataset.publicCategory;
            const active = !query && slug === (root.dataset.selectedCategory || 'all');
            filter.classList.toggle('is-active', active);
            filter.setAttribute('aria-current', active ? 'page' : 'false');
        });

        if (searchStatus) {
            searchStatus.textContent = query
                ? toFa(visible) + ' نتیجه برای «' + search.value.trim() + '»'
                : '';
        }

        if (searchEmpty) {
            searchEmpty.hidden = !query || visible > 0;
        }
    };

    filters.forEach(filter => {
        filter.addEventListener('click', event => {
            const slug = filter.dataset.publicCategory;

            if (slug === 'all') {
                event.preventDefault();
                if (search) search.value = '';
                window.history.replaceState(null, '', filter.getAttribute('href') || '/menu');
                apply();
                window.scrollTo({ top: 0, behavior: reducedMotion ? 'auto' : 'smooth' });
                return;
            }

            if (!hasFullMenu || search?.value.trim()) return;

            event.preventDefault();

                const target = root.querySelector('[data-public-section="' + slug + '"]');
                filters.forEach(node => {
                    const active = node === filter;
                    node.classList.toggle('is-active', active);
                    node.setAttribute('aria-current', active ? 'page' : 'false');
                });

                target?.scrollIntoView({
                    behavior: reducedMotion ? 'auto' : 'smooth',
                    block: 'start',
                });
                window.history.replaceState(null, '', filter.getAttribute('href') || '/menu');
            }
        });
    });

    search?.addEventListener('input', apply);

    clearButton?.addEventListener('click', () => {
        if (!search) return;
        search.value = '';
        apply();
        search.focus({ preventScroll: true });
    });

    root.querySelectorAll('img').forEach(image => {
        image.addEventListener('error', () => {
            image.hidden = true;
            image.closest(
                '.public-product-card__media, .public-featured-card__media, .public-menu__hero-media'
            )?.classList.add('is-media-failed');
        }, { once: true });
    });

    apply();

    function toFa(value) {
        return String(value).replace(/\d/g, digit => '۰۱۲۳۴۵۶۷۸۹'[digit]);
    }
})();
