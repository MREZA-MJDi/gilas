(() => {
    const container = document.getElementById('container');
    const switchButton = document.getElementById('switch');
    if (!container || !switchButton) return;

    const hexagons = [...container.querySelectorAll('.hexagon')];
    if (!hexagons.length) return;

    let rippleRunning = false;

    const ripple = (target) => {
        if (rippleRunning) return;
        rippleRunning = true;

        const targetRect = target.getBoundingClientRect();
        const data = hexagons
            .map((element) => {
                const rect = element.getBoundingClientRect();
                const distance = Math.hypot(
                    rect.x - targetRect.x,
                    rect.y - targetRect.y
                );

                return { element, distance };
            })
            .sort((a, b) => a.distance - b.distance);

        const maxDistance = data.at(-1)?.distance || 1;

        data.forEach(({ element, distance }) => {
            element.style.setProperty(
                '--ripple-factor',
                String((distance * 100) / maxDistance)
            );
        });

        container.classList.add('show-ripple');

        const cleanUp = () => {
            requestAnimationFrame(() => {
                container.classList.remove('show-ripple');
                data.forEach(({ element }) => element.style.removeProperty('--ripple-factor'));
                rippleRunning = false;
            });
        };

        const last = data.at(-1)?.element;
        if (!last) return cleanUp();

        last.addEventListener('animationend', cleanUp, { once: true });
    };

    hexagons.forEach((hexagon) => {
        hexagon.addEventListener('click', () => ripple(hexagon));
    });

    const syncTheme = (checked) => {
        document.body.classList.toggle('vision-ui', checked);
        switchButton.classList.toggle('is-checked', checked);
        switchButton.setAttribute('aria-pressed', String(checked));
    };

    switchButton.addEventListener('click', () => {
        syncTheme(!document.body.classList.contains('vision-ui'));
    });

    window.setTimeout(() => {
        const first = hexagons[0];
        if (!first) return;

        ripple(first);
        window.setTimeout(() => {
            syncTheme(true);
            window.setTimeout(() => ripple(first), 650);
        }, 950);
    }, 400);
})();