import './bootstrap';

(() => {
    const links = [...document.querySelectorAll('[data-admin-link]')];
    const sections = [...document.querySelectorAll('.admin-section[id]')];
    if (!links.length || !sections.length) return;

    const activate = id => {
        links.forEach(link => {
            const active = link.getAttribute('href') === '#' + id;
            link.classList.toggle('is-active', active);
        });
    };

    const observer = new IntersectionObserver(entries => {
        const visible = entries
            .filter(entry => entry.isIntersecting)
            .sort((a, b) => b.intersectionRatio - a.intersectionRatio)[0];

        if (visible) activate(visible.target.id);
    }, {
        rootMargin: '-15% 0px -65% 0px',
        threshold: [0, .25, .5],
    });

    sections.forEach(section => observer.observe(section));

    links.forEach(link => {
        link.addEventListener('click', event => {
            const target = document.querySelector(link.getAttribute('href'));
            if (!target) return;
            event.preventDefault();
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            history.replaceState(null, '', link.getAttribute('href'));
            activate(target.id);
        });
    });
})();
