(() => {
    const root = document.querySelector('#public-menu');
    if (!root) return;

    const categories = Array.isArray(window.GilasPublicMenu) ? window.GilasPublicMenu : [];
    const categoryMap = new Map(categories.map(category => [String(category.id), category]));
    const itemMap = new Map();

    categories.forEach(category => {
        (category.items || []).forEach(item => {
            itemMap.set(String(item.id), { ...item, categoryId: category.id, categoryName: category.name });
        });
    });

    const reducedMotion = window.matchMedia?.('(prefers-reduced-motion: reduce)').matches ?? false;
    const currency = root.dataset.currency || '';
    const orderUrl = root.dataset.orderUrl || '';
    const cartStorageKey = 'gilas:public-cart:v1:' + (root.dataset.tableToken || 'guest');
    const $ = selector => document.querySelector(selector);
    const all = selector => [...document.querySelectorAll(selector)];

    const initialCategoryId = root.dataset.initialCategory || categories[0]?.id || null;
    const initialCategory = categoryMap.get(String(initialCategoryId)) || categories[0];
    const initialItemId = root.dataset.initialItem || initialCategory?.items?.[0]?.id || null;

    const state = {
        categoryId: initialCategory?.id ?? null,
        itemId: initialItemId,
        cart: loadCart(),
        dialogItem: null,
        dialogQuantity: 1,
        submitting: false,
    };

    const image = $('[data-focus-image]');
    const fallback = $('[data-focus-fallback]');
    const focus = $('.menu-focus');

    bind();
    sync();

    function bind() {
        all('[data-category-select]').forEach(button => {
            button.addEventListener('click', () => selectCategory(button.dataset.categorySelect));
        });

        document.addEventListener('click', event => {
            const itemButton = event.target.closest('[data-item-select]');
            if (itemButton) {
                selectItem(itemButton.dataset.itemSelect);
            }

            if (event.target.closest('[data-focus-add]')) {
                addFocusedItem();
            }

            if (event.target.closest('[data-focus-open-details]')) {
                openDialog(state.itemId);
            }

            const related = event.target.closest('[data-related-item]');
            if (related) {
                selectItem(related.dataset.relatedItem);
            }

            const plus = event.target.closest('[data-cart-plus]');
            if (plus) updateCartQuantity(Number(plus.dataset.cartPlus), 1);

            const minus = event.target.closest('[data-cart-minus]');
            if (minus) updateCartQuantity(Number(minus.dataset.cartMinus), -1);

            const remove = event.target.closest('[data-cart-remove]');
            if (remove) removeCartLine(Number(remove.dataset.cartRemove));

            const addDialog = event.target.closest('[data-dialog-add]');
            if (addDialog) addDialogItem();

            const closeDialog = event.target.closest('[data-close-menu-dialog]');
            if (closeDialog) closeDialogBox();

            if (event.target.closest('[data-close-cart]')) closeCart();

            if (event.target.closest('[data-open-cart]')) openCart();

            if (event.target.closest('[data-public-submit-order]')) submitOrder();
        });

        document.addEventListener('keydown', event => {
            if (event.key !== 'Escape') return;
            const dialog = $('#public-menu-item-sheet');
            const cart = $('#public-menu-cart');
            if (dialog && !dialog.hidden) closeDialogBox();
            else if (cart && !cart.hidden) closeCart();
        });

        window.addEventListener('hashchange', restoreFromHash);

        all('img').forEach(node => {
            node.addEventListener('error', () => {
                node.hidden = true;
                node.closest('.menu-item-button__thumb')?.classList.add('is-media-failed');
            }, { once: true });
        });

        all('input[name="public-order-type"], input[name="public-payment-method"]').forEach(input => {
            input.addEventListener('change', () => {
                syncCheckoutFields();
                syncCart();
            });
        });

        all('#public-customer-name, #public-customer-phone, #public-customer-email, #public-customer-address, #public-customer-postal, #public-customer-note')
            .forEach(input => input.addEventListener('input', () => syncCart()));

        syncCheckoutFields();
    }

    function selectCategory(categoryId) {
        const category = categoryMap.get(String(categoryId));
        const firstItem = category?.items?.[0];
        if (!category || !firstItem) return;

        state.categoryId = category.id;
        state.itemId = firstItem.id;
        sync();
        updateHash();
    }

    function selectItem(itemId) {
        const item = itemMap.get(String(itemId));
        if (!item) return;

        state.categoryId = item.categoryId;
        state.itemId = item.id;
        sync();
        updateHash();
    }

    function sync() {
        const category = categoryMap.get(String(state.categoryId)) || categories[0];
        const item = itemMap.get(String(state.itemId)) || category?.items?.[0];

        if (!category || !item) return;

        state.categoryId = category.id;
        state.itemId = item.id;

        all('[data-category-select]').forEach(button => {
            const active = String(button.dataset.categorySelect) === String(category.id);
            button.classList.toggle('is-active', active);
            button.setAttribute('aria-pressed', String(active));
        });

        renderCategoryItems(category, item.id);
        updateFocus(category, item);
        renderRelated(item);
    }

    function renderCategoryItems(category, activeItemId) {
        const list = $('[data-item-list]');
        const heading = $('[data-items-heading]');
        if (!list) return;

        if (heading) heading.textContent = category.name;

        list.innerHTML = (category.items || []).map(item => {
            const active = String(item.id) === String(activeItemId);
            return '<button type="button" class="menu-item-button' + (active ? ' is-active' : '') + '" data-item-select="' + item.id + '" aria-pressed="' + active + '">' +
                '<span class="menu-item-button__thumb">' +
                    (item.image
                        ? '<img src="' + escapeHtml(item.image) + '" alt="" width="96" height="96" loading="lazy" decoding="async">'
                        : '<span>گ</span>') +
                '</span>' +
                '<span class="menu-item-button__copy"><strong>' + escapeHtml(item.name) + '</strong><small>' + formatPrice(item.price) + ' ' + escapeHtml(currency) + '</small></span>' +
                '<span class="menu-item-button__arrow" aria-hidden="true">↘</span>' +
            '</button>';
        }).join('');
    }

    function updateFocus(category, item) {
        const shouldAnimate = !reducedMotion && focus && focus.dataset.ready === '1';

        if (shouldAnimate) {
            focus.classList.remove('is-changing');
            requestAnimationFrame(() => focus.classList.add('is-changing'));
        }

        const imageReady = () => {
            if (!image) return;
            image.hidden = !item.image;
            if (item.image) image.removeAttribute('hidden');
            if (!item.image) {
                image.src = '';
                fallback?.removeAttribute('hidden');
                return;
            }

            fallback?.setAttribute('hidden', '');
            image.alt = item.name;
            image.src = item.image;
            if (image.complete) {
                finishFocusAnimation();
            } else {
                image.addEventListener('load', finishFocusAnimation, { once: true });
            }
        };

        $('[data-focus-category]').textContent = category.name;
        $('[data-current-category]').textContent = category.name;
        $('[data-current-item]').textContent = item.name;
        $('[data-focus-name]').textContent = item.name;
        $('[data-focus-description]').textContent = item.description || 'توضیحات این آیتم را از منوی اصلی دنبال کن.';
        $('[data-focus-price]').textContent = formatPrice(resolvePreviewPrice(item));

        if (item.image) {
            imageReady();
        } else {
            image?.setAttribute('hidden', '');
            fallback?.removeAttribute('hidden');
            finishFocusAnimation();
        }

        focus?.setAttribute('data-ready', '1');

        const stateLabel = $('[data-focus-state]');
        if (stateLabel) {
            stateLabel.textContent = item.variants?.length || item.options?.length ? 'قابل شخصی‌سازی' : 'موجود';
        }
    }

    function finishFocusAnimation() {
        if (!focus) return;
        requestAnimationFrame(() => focus.classList.remove('is-changing'));
    }

    function renderRelated(item) {
        const container = $('[data-related-list]');
        if (!container) return;

        const currentCategory = categoryMap.get(String(item.categoryId));
        const sameCategory = (currentCategory?.items || [])
            .filter(candidate => Number(candidate.id) !== Number(item.id));

        const crossCategory = categories
            .filter(category => Number(category.id) !== Number(item.categoryId))
            .flatMap(category => (category.items || []).slice(0, 2));

        const related = [...sameCategory.slice(0, 3), ...crossCategory.slice(0, 4)].slice(0, 6);

        container.innerHTML = related.length
            ? related.map(candidate => {
                const category = categoryMap.get(String(candidate.categoryId));
                return '<button type="button" class="menu-related-card" data-related-item="' + candidate.id + '">' +
                    '<span class="menu-related-card__media">' +
                        (candidate.image
                            ? '<img src="' + escapeHtml(candidate.image) + '" alt="" width="180" height="140" loading="lazy" decoding="async">'
                            : '<span>گ</span>') +
                    '</span>' +
                    '<span class="menu-related-card__copy">' +
                        '<small>' + escapeHtml(category?.name || '') + '</small>' +
                        '<strong>' + escapeHtml(candidate.name) + '</strong>' +
                        '<b>' + formatPrice(resolvePreviewPrice(candidate)) + ' ' + escapeHtml(currency) + '</b>' +
                    '</span>' +
                '</button>';
            }).join('')
            : '<p class="menu-related__empty">آیتم مرتبط دیگری در منو نیست.</p>';
    }

    function addFocusedItem() {
        const item = itemMap.get(String(state.itemId));
        if (!item) return;

        if (item.variants?.length || item.options?.length) {
            openDialog(item.id);
            return;
        }

        addToCart({
            menu_item_id: item.id,
            menu_item_variant_id: null,
            option_value_ids: [],
            quantity: 1,
            note: '',
            name: item.name,
            unit_price: resolvePreviewPrice(item),
        });

        toast('به سبد سفارش اضافه شد.');
    }

    function openDialog(itemId) {
        const item = itemMap.get(String(itemId));
        if (!item) return;

        state.dialogItem = item;
        state.dialogQuantity = 1;

        const panel = $('#public-menu-item-sheet');
        const content = $('[data-dialog-content]');
        if (!panel || !content) return;

        content.innerHTML =
            '<div class="menu-dialog__item-head">' +
                '<div class="menu-dialog__item-media">' +
                    (item.image ? '<img src="' + escapeHtml(item.image) + '" alt="' + escapeHtml(item.name) + '" width="420" height="420">' : '<span>گ</span>') +
                '</div>' +
                '<div><span class="eyebrow">' + escapeHtml(item.categoryName || '') + '</span><h3>' + escapeHtml(item.name) + '</h3><p>' + escapeHtml(item.description || '') + '</p></div>' +
            '</div>' +
            renderVariantChoices(item) +
            renderOptionChoices(item) +
            '<div class="menu-dialog__note"><label>یادداشت این آیتم</label><textarea data-dialog-note rows="2" maxlength="300" placeholder="مثلاً بدون یخ..."></textarea></div>' +
            '<div class="menu-dialog__actions"><div class="menu-dialog__qty"><button type="button" data-dialog-qty-minus>−</button><span data-dialog-qty>۱</span><button type="button" data-dialog-qty-plus>+</button></div><button type="button" class="menu-primary-action" data-dialog-add>افزودن <span data-dialog-total>' + formatPrice(resolvePreviewPrice(item)) + ' ' + escapeHtml(currency) + '</span></button></div>' +
            '<div class="menu-dialog__error" data-dialog-error hidden></div>';

        bindDialogControls(item);
        panel.hidden = false;
        panel.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-hidden');
        content.querySelector('input, button, textarea')?.focus({ preventScroll: true });
    }

    function renderVariantChoices(item) {
        if (!item.variants?.length) return '';

        return '<fieldset class="menu-choice-group"><legend>انتخاب اندازه</legend><div class="menu-choice-grid">' +
            item.variants.map((variant, index) =>
                '<label class="menu-choice"><span><strong>' + escapeHtml(variant.name) + '</strong><small>' + formatPrice(variant.price) + ' ' + escapeHtml(currency) + '</small></span><input type="radio" name="public-menu-variant" value="' + variant.id + '" ' + (index === 0 ? 'checked' : '') + '></label>'
            ).join('') +
        '</div></fieldset>';
    }

    function renderOptionChoices(item) {
        return (item.options || []).map(option => {
            if (!option.values?.length) return '';
            const inputType = option.max === 1 ? 'radio' : 'checkbox';
            const min = option.required ? Math.max(1, option.min || 0) : option.min || 0;
            return '<fieldset class="menu-choice-group" data-dialog-option-group data-dialog-option-min="' + min + '" data-dialog-option-max="' + (option.max || 0) + '" data-dialog-option-required="' + (option.required ? '1' : '0') + '">' +
                '<legend>' + escapeHtml(option.name) + '<small>' + (min ? 'حداقل ' + toFa(min) + ' انتخاب' : 'اختیاری') + '</small></legend>' +
                '<div class="menu-choice-list">' +
                    option.values.map(value =>
                        '<label class="menu-choice"><span><strong>' + escapeHtml(value.name) + '</strong><small>' + deltaLabel(value.priceDelta) + '</small></span><input type="' + inputType + '" name="public-menu-option-' + option.id + '" value="' + value.id + '" data-dialog-option></label>'
                    ).join('') +
                '</div>' +
            '</fieldset>';
        }).join('');
    }

    function bindDialogControls(item) {
        all('#public-menu-item-sheet input, #public-menu-item-sheet textarea').forEach(input => {
            input.addEventListener('change', refreshDialog);
        });

        all('[data-dialog-qty-minus]').forEach(button => button.addEventListener('click', () => {
            state.dialogQuantity = Math.max(1, state.dialogQuantity - 1);
            refreshDialog();
        }));

        all('[data-dialog-qty-plus]').forEach(button => button.addEventListener('click', () => {
            state.dialogQuantity = Math.min(1000, state.dialogQuantity + 1);
            refreshDialog();
        }));

        refreshDialog();

        function refreshDialog() {
            all('[data-dialog-option]').forEach(input => {
                if (input.type !== 'checkbox') return;
                const group = input.closest('[data-dialog-option-group]');
                const max = Number(group?.dataset.dialogOptionMax || 0);
                if (max > 0 && group.querySelectorAll('[data-dialog-option]:checked').length > max) {
                    input.checked = false;
                }
            });

            const selection = dialogSelection(item);
            const price = resolvePrice(item, selection.variantId, selection.optionValueIds);
            const total = $('[data-dialog-total]');
            const qty = $('[data-dialog-qty]');

            if (total) total.textContent = formatPrice(price * state.dialogQuantity) + ' ' + currency;
            if (qty) qty.textContent = toFa(state.dialogQuantity);

            const error = $('[data-dialog-error]');
            const errors = validateSelection(item, selection.optionValueIds);
            if (error) {
                error.hidden = !errors.length;
                error.textContent = errors[0] || '';
            }
        }
    }

    function addDialogItem() {
        const item = state.dialogItem;
        if (!item) return;

        const selection = dialogSelection(item);
        const errors = validateSelection(item, selection.optionValueIds);
        const error = $('[data-dialog-error]');

        if (errors.length) {
            if (error) {
                error.hidden = false;
                error.textContent = errors[0];
            }
            return;
        }

        const note = $('#public-menu-item-sheet [data-dialog-note]')?.value.trim().slice(0, 300) || '';
        const unitPrice = resolvePrice(item, selection.variantId, selection.optionValueIds);

        addToCart({
            menu_item_id: item.id,
            menu_item_variant_id: selection.variantId,
            option_value_ids: selection.optionValueIds,
            quantity: state.dialogQuantity,
            note,
            name: item.name,
            variant_name: selection.variantId
                ? item.variants.find(variant => Number(variant.id) === Number(selection.variantId))?.name || null
                : null,
            unit_price: unitPrice,
        });

        closeDialogBox();
        toast('به سبد سفارش اضافه شد.');
    }

    function dialogSelection(item) {
        const variant = $('#public-menu-item-sheet input[name="public-menu-variant"]:checked');
        return {
            variantId: variant ? Number(variant.value) : null,
            optionValueIds: [...(document.querySelectorAll('#public-menu-item-sheet [data-dialog-option]:checked') || [])].map(input => Number(input.value)),
        };
    }

    function validateSelection(item, selectedIds) {
        const errors = [];

        (item.options || []).forEach(option => {
            const selected = selectedIds.filter(id => option.values.some(value => Number(value.id) === Number(id))).length;
            const min = option.required ? Math.max(1, option.min || 0) : option.min || 0;
            const max = option.max || 0;

            if (selected < min) errors.push('لطفاً حداقل ' + toFa(min) + ' مورد از «' + option.name + '» را انتخاب کن.');
            if (max > 0 && selected > max) errors.push('برای «' + option.name + '» بیشتر از ' + toFa(max) + ' انتخاب مجاز نیست.');
        });

        return errors;
    }

    function resolvePrice(item, variantId, optionIds) {
        const variant = variantId
            ? item.variants.find(entry => Number(entry.id) === Number(variantId))
            : null;

        const base = Number(variant?.price ?? item.price ?? 0);
        const chosen = new Set(optionIds.map(Number));

        const delta = (item.options || []).reduce((sum, option) => sum + option.values
            .filter(value => chosen.has(Number(value.id)))
            .reduce((inner, value) => inner + Number(value.priceDelta || 0), 0), 0);

        return Math.max(0, base + delta);
    }

    function resolvePreviewPrice(item) {
        if (item.variants?.length) return Number(item.variants[0].price || item.price || 0);
        return Number(item.price || 0);
    }

    function addToCart(line) {
        const signature = makeSignature(line.menu_item_id, line.menu_item_variant_id, line.option_value_ids, line.note);
        const existing = state.cart.find(entry => entry.signature === signature);

        if (existing) {
            existing.quantity = Math.min(1000, Number(existing.quantity) + Number(line.quantity));
        } else {
            state.cart.push({ ...line, signature });
        }

        saveCart();
        syncCart();
    }

    function updateCartQuantity(index, delta) {
        const line = state.cart[index];
        if (!line) return;

        line.quantity = Math.min(1000, Math.max(0, Number(line.quantity) + delta));
        if (line.quantity <= 0) state.cart.splice(index, 1);

        saveCart();
        syncCart();
    }

    function removeCartLine(index) {
        state.cart.splice(index, 1);
        saveCart();
        syncCart();
    }

    function syncCart() {
        const count = state.cart.reduce((sum, line) => sum + Number(line.quantity), 0);
        const total = state.cart.reduce((sum, line) => sum + Number(line.unit_price) * Number(line.quantity), 0);
        const headerCount = root.querySelector('[data-header-cart-count]');

        if (headerCount) headerCount.textContent = toFa(count);

        const items = $('[data-public-cart-items]');
        const empty = $('[data-public-cart-empty]');
        const totalNode = $('[data-public-cart-total]');
        const submit = $('[data-public-submit-order]');

        if (totalNode) totalNode.textContent = formatPrice(total);
        if (submit) submit.disabled = !state.cart.length || state.submitting;

        if (empty) empty.hidden = state.cart.length > 0;

        if (items) {
            items.innerHTML = state.cart.map((line, index) =>
                '<article class="menu-cart-line">' +
                    '<div class="menu-cart-line__main"><strong>' + escapeHtml(line.name) + '</strong>' +
                        (line.variant_name ? '<small>' + escapeHtml(line.variant_name) + '</small>' : '') +
                        (line.note ? '<small>' + escapeHtml(line.note) + '</small>' : '') +
                    '</div>' +
                    '<div class="menu-cart-line__controls">' +
                        '<div class="menu-cart-line__qty"><button type="button" data-cart-plus="' + index + '">+</button><span>' + toFa(line.quantity) + '</span><button type="button" data-cart-minus="' + index + '">−</button></div>' +
                        '<strong>' + formatPrice(Number(line.unit_price) * Number(line.quantity)) + ' ' + escapeHtml(currency) + '</strong>' +
                        '<button type="button" data-cart-remove="' + index + '" aria-label="حذف">×</button>' +
                    '</div>' +
                '</article>'
            ).join('');
        }

        const hint = $('[data-public-cart-hint]');
        if (hint) {
            hint.textContent = orderUrl
                ? 'میز فعلی از QR تشخیص داده شده؛ فقط روش پرداخت را انتخاب کن.'
                : 'اول نوع دریافت، اطلاعات تماس و روش پرداخت را انتخاب کن.';
        }

        syncCheckoutFields();
    }

    function syncCheckoutFields() {
        const delivery = !orderUrl && document.querySelector('input[name="public-order-type"]:checked')?.value === 'delivery';
        const addressFields = document.querySelector('[data-public-address-fields]');
        if (addressFields) addressFields.hidden = !delivery;

        document.querySelectorAll('input[name="public-order-type"]').forEach(input => {
            input.closest('.menu-choice')?.classList.toggle('menu-choice--selected', input.checked);
        });

        document.querySelectorAll('input[name="public-payment-method"]').forEach(input => {
            input.closest('.menu-choice')?.classList.toggle('menu-choice--selected', input.checked);
        });
    }

    function checkoutPayload() {
        const orderType = orderUrl
            ? 'dine_in'
            : (document.querySelector('input[name="public-order-type"]:checked')?.value || 'pickup');
        const paymentMethod = document.querySelector('input[name="public-payment-method"]:checked')?.value || 'online';

        return {
            order_type: orderType,
            payment_method: paymentMethod,
            customer_name: $('#public-customer-name')?.value.trim() || '',
            phone: $('#public-customer-phone')?.value.trim() || '',
            email: $('#public-customer-email')?.value.trim() || null,
            address: $('#public-customer-address')?.value.trim() || null,
            postal_code: $('#public-customer-postal')?.value.trim() || null,
            customer_note: $('#public-customer-note')?.value.trim() || null,
        };
    }

    function openCart() {
        syncCart();
        openDialogElement($('#public-menu-cart'));
    }

    function closeCart() {
        closeDialogElement($('#public-menu-cart'));
    }

    function openDialogElement(node) {
        if (!node) return;
        node.hidden = false;
        node.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-hidden');
    }

    function closeDialogElement(node) {
        if (!node) return;
        node.hidden = true;
        node.setAttribute('aria-hidden', 'true');

        const itemDialog = $('#public-menu-item-sheet');
        const cartDialog = $('#public-menu-cart');
        if ((itemDialog?.hidden ?? true) && (cartDialog?.hidden ?? true)) {
            document.body.classList.remove('overflow-hidden');
        }
    }

    function closeDialogBox() {
        closeDialogElement($('#public-menu-item-sheet'));
    }

    async function submitOrder() {
        if (!orderUrl) {
            const alert = $('[data-public-cart-alert]');
            if (alert) {
                alert.hidden = false;
                alert.textContent = 'این منو برای مشاهده و انتخاب آزاد است؛ برای ثبت سفارش، QR میز را اسکن کن.';
            }
            return;
        }

        if (state.submitting || !state.cart.length) return;

        const checkout = checkoutPayload();
        const checkoutErrors = [];

        if (!orderUrl && !['pickup', 'delivery'].includes(checkout.order_type)) {
            checkoutErrors.push('نوع دریافت سفارش را انتخاب کن.');
        }

        if (!checkout.customer_name || checkout.customer_name.length < 2) {
            checkoutErrors.push('نام و نام خانوادگی را وارد کن.');
        }

        if (!checkout.phone || checkout.phone.length < 8) {
            checkoutErrors.push('شماره موبایل را وارد کن.');
        }

        if (!['online', 'cashier'].includes(checkout.payment_method)) {
            checkoutErrors.push('روش پرداخت را انتخاب کن.');
        }

        if (checkout.order_type === 'delivery' && (!checkout.address || checkout.address.length < 8)) {
            checkoutErrors.push('برای ارسال، آدرس کامل لازم است.');
        }

        if (checkoutErrors.length) {
            const alert = $('[data-public-cart-alert]');
            if (alert) {
                alert.hidden = false;
                alert.textContent = checkoutErrors[0];
            }
            return;
        }

        state.submitting = true;
        syncCart();

        const submitLabel = $('[data-public-submit-label]');
        const spinner = $('[data-public-submit-spinner]');
        if (submitLabel) submitLabel.textContent = 'در حال ثبت...';
        if (spinner) spinner.hidden = false;

        const idempotencyKey = globalThis.crypto?.randomUUID?.()
            || Date.now() + '-' + Math.random().toString(16).slice(2);

        try {
            const response = await fetch(orderUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'Idempotency-Key': idempotencyKey,
                },
                body: JSON.stringify({
                    ...checkout,
                    items: state.cart.map(line => ({
                        menu_item_id: line.menu_item_id,
                        menu_item_variant_id: line.menu_item_variant_id,
                        quantity: line.quantity,
                        option_value_ids: line.option_value_ids,
                        note: line.note || null,
                    })),
                    customer_note: ($('#public-customer-note')?.value || '').trim() || null,
                }),
            });

            const data = await response.json().catch(() => ({}));
            if (!response.ok) {
                throw new Error(firstError(data) || data.message || 'ثبت سفارش انجام نشد.');
            }

            state.cart = [];
            saveCart();
            window.location.href = data?.data?.tracking_url || ('/orders/' + encodeURIComponent(data?.data?.public_token || ''));
        } catch (error) {
            const alert = $('[data-public-cart-alert]');
            if (alert) {
                alert.hidden = false;
                alert.textContent = error.message || 'ثبت سفارش انجام نشد.';
            }
        } finally {
            state.submitting = false;
            if (submitLabel) submitLabel.textContent = 'ثبت سفارش';
            if (spinner) spinner.hidden = true;
            syncCart();
        }
    }

    function toast(message) {
        let box = document.querySelector('[data-public-menu-toast]');
        if (!box) {
            box = document.createElement('div');
            box.dataset.publicMenuToast = '';
            box.className = 'menu-toast';
            document.body.appendChild(box);
        }

        box.textContent = message;
        box.classList.add('is-visible');
        window.clearTimeout(box._timer);
        box._timer = window.setTimeout(() => box.classList.remove('is-visible'), 1900);
    }

    function restoreFromHash() {
        const hash = decodeURIComponent(window.location.hash || '');
        if (!hash.startsWith('#item=')) return;

        const item = [...itemMap.values()].find(entry => entry.slug === hash.slice(6));
        if (item) {
            state.categoryId = item.categoryId;
            state.itemId = item.id;
            sync();
        }
    }

    function updateHash() {
        const item = itemMap.get(String(state.itemId));
        if (!item?.slug) return;

        const next = '#item=' + encodeURIComponent(item.slug);
        if (window.location.hash !== next) history.replaceState(null, '', window.location.pathname + window.location.search + next);
    }

    function saveCart() {
        try {
            localStorage.setItem(cartStorageKey, JSON.stringify(state.cart));
        } catch (_) {
            // Storage can be disabled; the menu remains fully browsable.
        }
    }

    function loadCart() {
        try {
            const raw = JSON.parse(localStorage.getItem(cartStorageKey) || '[]');
            if (!Array.isArray(raw)) return [];

            return raw.map(line => {
                const item = itemMap.get(String(line?.menu_item_id));
                if (!item) return null;

                const variantId = line.menu_item_variant_id ? Number(line.menu_item_variant_id) : null;
                if (variantId && !item.variants.some(variant => Number(variant.id) === variantId)) return null;

                const optionIds = [...new Set(Array.isArray(line.option_value_ids) ? line.option_value_ids.map(Number) : [])]
                    .filter(id => item.options.some(option => option.values.some(value => Number(value.id) === id)));

                if (validateSelection(item, optionIds).length) return null;

                const quantity = Math.min(1000, Math.max(1, Number(line.quantity) || 1));
                const note = typeof line.note === 'string' ? line.note.slice(0, 300) : '';
                const unitPrice = resolvePrice(item, variantId, optionIds);

                return {
                    signature: makeSignature(item.id, variantId, optionIds, note),
                    menu_item_id: item.id,
                    menu_item_variant_id: variantId,
                    option_value_ids: optionIds,
                    quantity,
                    note,
                    name: item.name,
                    variant_name: variantId ? item.variants.find(variant => Number(variant.id) === variantId)?.name || null : null,
                    unit_price: unitPrice,
                };
            }).filter(Boolean);
        } catch (_) {
            return [];
        }
    }

    function makeSignature(itemId, variantId, optionIds, note) {
        return [itemId, variantId || '', [...optionIds].sort((a, b) => a - b).join(','), note].join('|');
    }

    function firstError(data) {
        const values = Object.values(data?.errors || {});
        const first = values.find(value => Array.isArray(value) && value.length);
        return first?.[0] || null;
    }

    function deltaLabel(value) {
        value = Number(value || 0);
        return value > 0
            ? '+' + formatPrice(value)
            : value < 0
                ? formatPrice(value)
                : 'بدون تغییر';
    }

    function formatPrice(value) {
        return new Intl.NumberFormat('fa-IR').format(Math.round(Number(value) || 0));
    }

    function toFa(value) {
        return String(value).replace(/\d/g, digit => '۰۱۲۳۴۵۶۷۸۹'[digit]);
    }

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, character => ({
            '&':'&amp;',
            '<':'&lt;',
            '>':'&gt;',
            '"':'&quot;',
            "'":'&#039;',
        }[character]));
    }
})();