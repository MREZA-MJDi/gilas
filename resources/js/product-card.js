(() => {
    const root = document.querySelector('[data-gilas-product-card]');
    if (!root) return;

    const stage = root.querySelector('[data-product-stage]');
    const imageContainer = root.querySelector('[data-image-container]');
    const layers = [...root.querySelectorAll('[data-product-layer]')];
    const layerStack = root.querySelector('[data-product-layer-stack]');
    const reducedMotion = window.matchMedia?.('(prefers-reduced-motion: reduce)').matches ?? false;

    let products = [];
    try { products = JSON.parse(root.dataset.products || '[]'); } catch (_) { products = []; }
    products = products.filter(item => item && item.image).slice(0, 8);

    if (!products.length || !stage || !imageContainer || !layerStack || !layers.length) return;

    const name = root.querySelector('[data-product-name]');
    const category = root.querySelector('[data-product-category]');
    const description = root.querySelector('[data-product-description]');
    const price = root.querySelector('[data-product-price]');
    const position = root.querySelector('[data-product-position]');
    const action = root.querySelector('[data-product-action]');
    const dots = root.querySelector('[data-product-dots]');
    const shapeButtons = [...root.querySelectorAll('[data-shape]')];

    const state = { index:0, shape:'rectangle', pointerX:.5, pointerY:.5, dragging:false, threeD:true, parallax:true, tilt:24, pan:58, depth:32, amp:80, angle:0 };
    const clips = {
        rectangle:'polygon(0 0,100% 0,100% 100%,0 100%)',
        circle:'circle(45% at 50% 50%)',
        diamond:'polygon(50% 0,14.1% 50%,50% 100%,85.9% 50%)',
        hexagon:'polygon(25% 6.7%,75% 6.7%,100% 50%,75% 93.3%,25% 93.3%,0 50%)'
    };

    const renderDots = () => {
        if (!dots) return;
        dots.innerHTML = '';
        products.forEach((item, i) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = i === state.index ? 'is-active' : '';
            button.setAttribute('aria-label', item.name || ('محصول ' + (i + 1)));
            button.addEventListener('click', () => go(i));
            dots.appendChild(button);
        });
    };

    const setMotion = () => {
        const dx = state.pointerX - .5;
        const dy = state.pointerY - .5;
        const rotateY = state.threeD ? dx * state.tilt : 0;
        const rotateX = state.threeD ? -dy * state.tilt : 0;
        const driftX = state.parallax ? dx * state.pan : 0;
        const driftY = state.parallax ? dy * state.pan * .18 : 0;

        layerStack.style.transform =
            'translate3d(' + (driftX * .12) + 'px,' + driftY + 'px,0) rotateX(' + rotateX + 'deg) rotateY(' + (rotateY + state.angle) + 'deg)';

        layers.forEach((layer, i) => {
            const z = i * state.depth;
            const px = state.parallax ? dx * state.amp * (1 - i * .05) : 0;
            const py = state.parallax ? dy * state.amp * .4 * (1 - i * .05) : 0;
            layer.style.transform =
                'translate3d(' + px + 'px,' + py + 'px,' + z + 'px) rotateZ(' + (state.angle * i * .15) + 'deg) scale(' + (1 - i * .008) + ')';
            layer.style.opacity = i === 0 ? '1' : '.03';
            layer.style.filter = 'none';
        });
    };

    const render = () => {
        const product = products[state.index];
        name.textContent = product.name || 'محصول گیلاس';
        category.textContent = product.category || 'منو';
        description.textContent = product.description || 'یک انتخاب خوش‌طعم از خانه گیلاسی.';
        price.textContent = product.price_label || '';
        position.textContent = String(state.index + 1).padStart(2,'0') + ' / ' + String(products.length).padStart(2,'0');
        layers.forEach(layer => {
            layer.style.backgroundImage = 'url("' + String(product.image).replaceAll('"','%22') + '")';
            layer.style.clipPath = clips[state.shape];
        });
        if (action) action.href = product.url || '/menu';
        renderDots();
        setMotion();
    };

    const go = next => {
        state.index = (next + products.length) % products.length;
        state.pointerX = .5;
        state.pointerY = .5;
        state.angle = 0;
        render();
    };

    shapeButtons.forEach(button => button.addEventListener('click', () => {
        state.shape = button.dataset.shape || 'rectangle';
        shapeButtons.forEach(item => item.classList.toggle('is-active', item === button));
        layers.forEach(layer => layer.style.clipPath = clips[state.shape]);
    }));

    root.querySelector('[data-product-next]')?.addEventListener('click', () => go(state.index + 1));
    root.querySelector('[data-product-prev]')?.addEventListener('click', () => go(state.index - 1));

    stage.addEventListener('pointermove', event => {
        const rect = imageContainer.getBoundingClientRect();
        if (!rect.width || !rect.height) return;
        state.pointerX = Math.min(1, Math.max(0, (event.clientX - rect.left) / rect.width));
        state.pointerY = Math.min(1, Math.max(0, (event.clientY - rect.top) / rect.height));
        setMotion();
    });

    stage.addEventListener('pointerdown', event => {
        if (event.pointerType === 'mouse' && event.button !== 0) return;
        state.dragging = true;
        imageContainer.classList.add('is-dragging');
        stage.setPointerCapture?.(event.pointerId);
    });

    const release = () => {
        state.dragging = false;
        imageContainer.classList.remove('is-dragging');
    };

    stage.addEventListener('pointerup', release);
    stage.addEventListener('pointercancel', release);

    let touchStartX = null;
    stage.addEventListener('touchstart', event => { touchStartX = event.touches[0]?.clientX ?? null; }, { passive:true });
    stage.addEventListener('touchend', event => {
        if (touchStartX === null) return;
        const endX = event.changedTouches[0]?.clientX ?? touchStartX;
        const delta = endX - touchStartX;
        if (Math.abs(delta) > 48) go(delta < 0 ? state.index + 1 : state.index - 1);
        touchStartX = null;
    }, { passive:true });

    stage.addEventListener('keydown', event => {
        if (event.key === 'ArrowLeft') go(state.index + 1);
        if (event.key === 'ArrowRight') go(state.index - 1);
    });

    if (!reducedMotion) {
        let last = performance.now();
        const tick = now => {
            const delta = Math.min(42, now - last);
            last = now;
            if (!state.dragging) { state.angle += delta * .0038; setMotion(); }
            requestAnimationFrame(tick);
        };
        requestAnimationFrame(tick);
    }

    shapeButtons[0]?.classList.add('is-active');
    render();
})();
