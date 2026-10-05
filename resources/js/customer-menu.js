const reduceMotion = window.matchMedia?.('(prefers-reduced-motion: reduce)').matches ?? false;

(() => {
    const root = document.querySelector('#customer-menu');
    if (!root) return;

    const data = window.GilasMenu?.data || [];
    const items = new Map();
    data.forEach(category => (category.items || []).forEach(item => items.set(String(item.id), item)));

    const cartKey = 'gilas:cart:v2:' + (root.dataset.tableToken || 'public');
    const state = {
        cart: loadCart(),
        current: null,
        quantity: 1,
        submitting: false,
        checkoutKey: null,
    };

    const $ = (selector) => root.querySelector(selector);
    const all = (selector) => [...root.querySelectorAll(selector)];
    const currency = root.dataset.currency || '';

    const itemSheet = $('#item-sheet');
    const itemContent = $('#sheet-content');
    const cartSheet = $('#cart-sheet');
    const successSheet = $('#success-sheet');
    const submitButton = $('[data-submit-order]');
    const search = $('#menu-search');

    bind();
    renderCart();

    function bind() {
        all('[data-open-item]').forEach((button) => {
            button.addEventListener('click', () => openItem(button.dataset.openItem));
        });

        all('[data-category]').forEach((button) => {
            button.addEventListener('click', () => {
                const section = root.querySelector('[data-category-section="' + CSS.escape(button.dataset.category) + '"]');
                section?.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'start' });
                all('[data-category]').forEach((node) => node.classList.toggle('is-active', node === button));
            });
        });

        search?.addEventListener('input', () => filterMenu(search.value));

        all('[data-close-sheet]').forEach((button) => button.addEventListener('click', closeItem));
        all('[data-close-cart]').forEach((button) => button.addEventListener('click', closeCart));
        $('[data-open-cart]')?.addEventListener('click', openCart);
        $('[data-close-success]')?.addEventListener('click', closeSuccess);
        submitButton?.addEventListener('click', submitOrder);

        document.addEventListener('keydown', (event) => {
            if (event.key !== 'Escape') return;
            if (successSheet?.classList.contains('is-open')) closeSuccess();
            else if (cartSheet?.classList.contains('is-open')) closeCart();
            else if (itemSheet?.classList.contains('is-open')) closeItem();
        });
    }

    function openItem(id) {
        const item = items.get(String(id));
        if (!item) return;

        state.current = item;
        state.quantity = 1;

        itemContent.innerHTML = renderItem(item);
        bindItem(item);
        openSheet(itemSheet);
    }

    function renderItem(item) {
        const image = item.image
            ? '<div class="sheet-product-image"><img src="' + escapeHtml(item.image) + '" alt="' + escapeHtml(item.name) + '" width="900" height="560"></div>'
            : '<div class="sheet-product-image"><div class="menu-card__no-image">تصویر محصول در دسترس نیست</div></div>';

        const variants = item.variants?.length
            ? '<div class="sheet-group"><h3>اندازه</h3><div class="sheet-options">' +
              item.variants.map((variant, index) => '<label class="sheet-option"><span><input type="radio" name="variant" value="' + Number(variant.id) + '" ' + (index === 0 ? 'checked' : '') + '> ' + escapeHtml(variant.name) + '</span><em>' + formatPrice(variant.price) + '</em></label>').join('') +
              '</div></div>'
            : '';

        const options = (item.options || []).filter(option => option.values?.length).map(option => {
            const inputType = Number(option.max) === 1 ? 'radio' : 'checkbox';
            const hint = Number(option.min) > 0 || option.required
                ? 'حداقل ' + fa(Math.max(1, Number(option.min) || 0)) + ' انتخاب'
                : 'اختیاری';
            return '<div class="sheet-group" data-option-group data-min="' + Number(option.min || 0) + '" data-max="' + Number(option.max || 0) + '" data-required="' + (option.required ? '1' : '0') + '">' +
                '<h3>' + escapeHtml(option.name) + ' <span class="text-[10px] font-normal text-[var(--muted)]">(' + hint + ')</span></h3>' +
                '<div class="sheet-options">' +
                option.values.map(value => '<label class="sheet-option"><span><input type="' + inputType + '" name="option-' + Number(option.id) + '" value="' + Number(value.id) + '" data-option-value> ' + escapeHtml(value.name) + '</span><em>' + delta(value.priceDelta) + '</em></label>').join('') +
                '</div></div>';
        }).join('');

        return image +
            '<div class="sheet-product-copy"><h2 id="sheet-title">' + escapeHtml(item.name) + '</h2>' +
            (item.description ? '<p>' + escapeHtml(item.description) + '</p>' : '') +
            '<div class="sheet-price"><span>قیمت</span><strong data-live-price>' + formatPrice(item.price) + ' ' + currency + '</strong></div></div>' +
            variants + options +
            '<div class="sheet-qty"><div class="sheet-qty__controls"><button type="button" data-qty-minus>−</button><span data-qty>۱</span><button type="button" data-qty-plus>+</button></div>' +
            '<button type="button" class="sheet-add" data-add-item>افزودن به سبد · <span data-live-total>' + formatPrice(item.price) + ' ' + currency + '</span></button></div>' +
            '<div class="sheet-error hidden" data-sheet-error></div>';
    }

    function bindItem(item) {
        const refresh = () => {
            const selection = selectionFromForm();
            const price = resolvePrice(item, selection.variantId, selection.optionValueIds);
            $('[data-live-price]').textContent = formatPrice(price) + ' ' + currency;
            $('[data-live-total]').textContent = formatPrice(price * state.quantity) + ' ' + currency;
            $('[data-qty]').textContent = fa(state.quantity);
        };

        all('#sheet-content input').forEach((input) => input.addEventListener('change', () => {
            if (input.matches('[data-option-value][type="checkbox"]')) {
                const group = input.closest('[data-option-group]');
                const max = Number(group?.dataset.max || 0);
                if (max > 0 && group.querySelectorAll('[data-option-value]:checked').length > max) {
                    input.checked = false;
                    toast('بیشتر از حد مجاز انتخاب کردی.');
                }
            }
            refresh();
        }));

        $('[data-qty-minus]')?.addEventListener('click', () => {
            state.quantity = Math.max(1, state.quantity - 1);
            refresh();
        });
        $('[data-qty-plus]')?.addEventListener('click', () => {
            state.quantity = Math.min(99, state.quantity + 1);
            refresh();
        });

        $('[data-add-item]')?.addEventListener('click', () => {
            const selection = selectionFromForm();
            const error = validateOptions(item, selection.optionValueIds);
            const box = $('[data-sheet-error]');
            if (error) {
                box.textContent = error;
                box.classList.remove('hidden');
                return;
            }

            const line = {
                signature: signature(item.id, selection.variantId, selection.optionValueIds),
                menu_item_id: Number(item.id),
                menu_item_variant_id: selection.variantId,
                option_value_ids: selection.optionValueIds,
                quantity: state.quantity,
                note: '',
                name: item.name,
                variant_name: selection.variantId
                    ? (item.variants.find(v => Number(v.id) === selection.variantId)?.name || '')
                    : '',
                unit_price: resolvePrice(item, selection.variantId, selection.optionValueIds),
            };

            const existing = state.cart.find((entry) => entry.signature === line.signature);
            if (existing) existing.quantity = Math.min(99, existing.quantity + line.quantity);
            else state.cart.push(line);

            saveCart();
            renderCart();
            closeItem();
            toast('به سبد سفارش اضافه شد.');
        });

        refresh();
    }

    function selectionFromForm() {
        const variant = itemContent.querySelector('input[name="variant"]:checked');
        return {
            variantId: variant ? Number(variant.value) : null,
            optionValueIds: [...itemContent.querySelectorAll('[data-option-value]:checked')].map(input => Number(input.value)),
        };
    }

    function validateOptions(item, selectedIds) {
        for (const option of (item.options || [])) {
            const selected = selectedIds.filter(id => (option.values || []).some(value => Number(value.id) === Number(id))).length;
            const min = option.required ? Math.max(1, Number(option.min || 0)) : Number(option.min || 0);
            const max = Number(option.max || 0);
            if (selected < min) return 'لطفاً حداقل ' + fa(min) + ' مورد از «' + option.name + '» را انتخاب کن.';
            if (max > 0 && selected > max) return 'برای «' + option.name + '» بیشتر از ' + fa(max) + ' انتخاب مجاز نیست.';
        }
        return '';
    }

    function resolvePrice(item, variantId, optionIds) {
        const variant = variantId ? item.variants?.find(v => Number(v.id) === Number(variantId)) : null;
        const base = Number(variant?.price ?? item.price ?? 0);
        const chosen = new Set(optionIds.map(Number));
        const deltaTotal = (item.options || []).reduce((sum, option) =>
            sum + (option.values || [])
                .filter(value => chosen.has(Number(value.id)))
                .reduce((inner, value) => inner + Number(value.priceDelta || 0), 0), 0);
        return Math.max(0, base + deltaTotal);
    }

    function renderCart() {
        const count = state.cart.reduce((sum, line) => sum + Number(line.quantity), 0);
        const total = state.cart.reduce((sum, line) => sum + Number(line.unit_price) * Number(line.quantity), 0);

        $('[data-cart-count]').textContent = fa(count);
        $('[data-cart-total]').textContent = formatPrice(total);
        if (submitButton) submitButton.disabled = state.cart.length === 0 || state.submitting;
        $('#cart-empty')?.classList.toggle('hidden', state.cart.length > 0);

        const list = $('#cart-items');
        if (!list) return;

        list.innerHTML = state.cart.map((line, index) =>
            '<article class="cart-line">' +
                '<div class="cart-line__top"><div><h3>' + escapeHtml(line.name) + '</h3>' +
                (line.variant_name ? '<p>' + escapeHtml(line.variant_name) + '</p>' : '') +
                '</div><button type="button" class="cart-remove" data-remove="' + index + '">حذف</button></div>' +
                '<div class="cart-line__bottom"><span>' +
                '<button type="button" class="cart-remove" data-minus="' + index + '">−</button> ' + fa(line.quantity) +
                ' <button type="button" class="cart-remove" data-plus="' + index + '">+</button></span>' +
                '<strong>' + formatPrice(line.unit_price * line.quantity) + ' ' + currency + '</strong></div>' +
            '</article>'
        ).join('');

        all('[data-remove]').forEach(button => button.addEventListener('click', () => {
            state.cart.splice(Number(button.dataset.remove), 1);
            saveCart(); renderCart();
        }));
        all('[data-plus]').forEach(button => button.addEventListener('click', () => {
            const line = state.cart[Number(button.dataset.plus)];
            if (line) line.quantity = Math.min(99, line.quantity + 1);
            saveCart(); renderCart();
        }));
        all('[data-minus]').forEach(button => button.addEventListener('click', () => {
            const line = state.cart[Number(button.dataset.minus)];
            if (!line) return;
            line.quantity -= 1;
            if (line.quantity <= 0) state.cart.splice(Number(button.dataset.minus), 1);
            saveCart(); renderCart();
        }));
    }

    async function submitOrder() {
        if (state.submitting || !state.cart.length) return;

        state.submitting = true;
        state.checkoutKey ||= globalThis.crypto?.randomUUID?.() || (Date.now() + '-' + Math.random().toString(16).slice(2));
        submitButton.disabled = true;
        $('[data-submit-label]').textContent = 'در حال ثبت…';
        $('[data-submit-spinner]').classList.remove('hidden');

        try {
            const response = await fetch(root.dataset.orderUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'Idempotency-Key': state.checkoutKey,
                },
                body: JSON.stringify({
                    customer_note: ($('#customer-note')?.value || '').trim() || null,
                    items: state.cart.map(line => ({
                        menu_item_id: line.menu_item_id,
                        menu_item_variant_id: line.menu_item_variant_id,
                        quantity: line.quantity,
                        option_value_ids: line.option_value_ids,
                        note: line.note || null,
                    })),
                }),
            });

            const payload = await response.json().catch(() => ({}));
            if (!response.ok) throw new Error(firstError(payload) || payload.message || 'ثبت سفارش انجام نشد.');

            state.cart = [];
            state.checkoutKey = null;
            saveCart(); renderCart();
            if ($('#customer-note')) $('#customer-note').value = '';
            closeCart();
            $('#success-order-number').textContent = payload?.data?.order_number || '—';
            $('#success-total').textContent = formatPrice(payload?.data?.total || 0);
            openSheet(successSheet);
        } catch (error) {
            const alert = $('#order-alert');
            if (alert) {
                alert.textContent = error.message || 'خطایی رخ داد. دوباره تلاش کن.';
                alert.classList.remove('hidden');
            }
        } finally {
            state.submitting = false;
            $('[data-submit-label]').textContent = 'ثبت سفارش';
            $('[data-submit-spinner]').classList.add('hidden');
            submitButton.disabled = !state.cart.length;
        }
    }

    function filterMenu(value) {
        const query = value.trim().toLocaleLowerCase('fa-IR');
        let visible = 0;
        all('[data-menu-card]').forEach(card => {
            const haystack = (card.dataset.search || '').toLocaleLowerCase('fa-IR');
            const hit = !query || haystack.includes(query);
            card.classList.toggle('hidden', !hit);
            if (hit) visible += 1;
        });
        all('[data-category-section]').forEach(section => {
            const has = [...section.querySelectorAll('[data-menu-card]')].some(card => !card.classList.contains('hidden'));
            section.classList.toggle('hidden', Boolean(query) && !has);
        });
        $('#empty-search')?.classList.toggle('hidden', Boolean(visible) || !query);
    }

    function openSheet(node) {
        if (!node) return;
        node.classList.add('is-open');
        node.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    }
    function closeSheet(node) {
        if (!node) return;
        node.classList.remove('is-open');
        node.setAttribute('aria-hidden', 'true');
        if (![itemSheet, cartSheet, successSheet].some(panel => panel?.classList.contains('is-open'))) {
            document.body.style.overflow = '';
        }
    }
    function openCart() { renderCart(); openSheet(cartSheet); }
    function closeCart() { closeSheet(cartSheet); }
    function closeItem() { closeSheet(itemSheet); state.current = null; }
    function closeSuccess() { closeSheet(successSheet); }

    function loadCart() {
        try {
            const saved = JSON.parse(localStorage.getItem(cartKey) || '[]');
            if (!Array.isArray(saved)) return [];
            return saved.map(line => {
                const item = items.get(String(line?.menu_item_id));
                if (!item) return null;
                const variantId = line.menu_item_variant_id ? Number(line.menu_item_variant_id) : null;
                if (variantId && !item.variants.some(v => Number(v.id) === variantId)) return null;
                const optionIds = [...new Set(Array.isArray(line.option_value_ids) ? line.option_value_ids.map(Number) : [])]
                    .filter(id => (item.options || []).some(option => (option.values || []).some(value => Number(value.id) === id)));
                if (validateOptions(item, optionIds)) return null;
                const quantity = Math.min(99, Math.max(1, Number(line.quantity) || 1));
                return {
                    signature: signature(item.id, variantId, optionIds),
                    menu_item_id: Number(item.id),
                    menu_item_variant_id: variantId,
                    option_value_ids: optionIds,
                    quantity,
                    note: '',
                    name: item.name,
                    variant_name: variantId ? (item.variants.find(v => Number(v.id) === variantId)?.name || '') : '',
                    unit_price: resolvePrice(item, variantId, optionIds),
                };
            }).filter(Boolean);
        } catch {
            return [];
        }
    }

    function saveCart() { localStorage.setItem(cartKey, JSON.stringify(state.cart)); }
    function signature(itemId, variantId, optionIds) { return [itemId, variantId || '', [...optionIds].sort((a,b) => a-b).join(',')].join('|'); }
    function firstError(payload) {
        const values = Object.values(payload?.errors || {});
        const first = values.find(value => Array.isArray(value) && value.length);
        return first?.[0] || null;
    }
    function formatPrice(value) { return new Intl.NumberFormat('fa-IR').format(Math.round(Number(value) || 0)); }
    function fa(value) { return String(value).replace(/\d/g, digit => '۰۱۲۳۴۵۶۷۸۹'[digit]); }
    function delta(value) {
        const n = Number(value || 0);
        return n > 0 ? '+' + formatPrice(n) : n < 0 ? formatPrice(n) : 'بدون تغییر';
    }
    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, (char) => ({ '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#039;' }[char]));
    }
    let toastTimer;
    function toast(message) {
        const box = $('#toast');
        if (!box) return;
        $('[data-toast-message]').textContent = message;
        box.classList.remove('opacity-0');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => box.classList.add('opacity-0'), 2100);
    }
})();
