document.addEventListener('DOMContentLoaded', () => {
    const links = [...document.querySelectorAll('.admin-nav a[href^="#"], .admin-mobile-nav a[href^="#"]')];
    const targets = links.map(link => document.querySelector(link.getAttribute('href'))).filter(Boolean);

    if (!targets.length) return;

    const observer = new IntersectionObserver((entries) => {
        const visible = entries.filter(entry => entry.isIntersecting).sort((a,b) => b.intersectionRatio-a.intersectionRatio)[0];
        if (!visible) return;
        links.forEach(link => link.classList.toggle('is-active', link.getAttribute('href') === '#' + visible.target.id));
    }, { rootMargin: '-25% 0px -60% 0px', threshold: [0, .25, .6] });

    targets.forEach(target => observer.observe(target));
});
