(() => {
    const root = document.querySelector('#customer-menu');
    if (!root) return;

    const items = new Map();
    (window.GilasMenu?.data || []).forEach(category => (category.items || []).forEach(item => items.set(String(item.id), item)));

    const cartKey = 'gilas:cart:v1:' + root.dataset.tableToken;
    const state = { cart: loadCart(), currentItem: null, checkoutKey: null, submitting: false };
    const $ = selector => document.querySelector(selector);
    const all = selector => [...document.querySelectorAll(selector)];

    const cartShell = $('[data-cart-shell]');
    const cartItems = $('#cart-items');
    const cartEmpty = $('#cart-empty');
    const cartCount = $('[data-cart-count]');
    const cartTotal = $('[data-cart-total]');
    const itemSheet = $('#item-sheet');
    const itemContent = $('#sheet-content');
    const cartSheet = $('#cart-sheet');
    const successSheet = $('#success-sheet');
    const alertBox = $('#order-alert');
    const submitButton = $('[data-submit-order]');
    const successTrackLink = $('#success-track-link');
    const search = $('#menu-search');

    bind();
    renderCart();

    function bind() {
        all('[data-open-item]').forEach(button => button.addEventListener('click', () => openItem(button.dataset.openItem)));
        all('[data-category]').forEach(button => button.addEventListener('click', () => {
            document.querySelector('[data-category-section="' + button.dataset.category + '"]')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            activateCategory(button.dataset.category);
        }));
        search?.addEventListener('input', () => filterMenu(search.value));
        all('[data-close-sheet]').forEach(button => button.addEventListener('click', closeItem));
        all('[data-close-cart]').forEach(button => button.addEventListener('click', closeCart));
        $('[data-open-cart]')?.addEventListener('click', openCart);
        $('[data-close-success]')?.addEventListener('click', closeSuccess);
        submitButton?.addEventListener('click', submitOrder);

        all('input[name="payment-method"]').forEach(input => {
            input.addEventListener('change', () => {
                all('.table-payment-choice').forEach(choice => {
                    choice.classList.toggle('is-selected', choice.querySelector('input')?.checked === true);
                });
            });
        });

        document.addEventListener('keydown', event => {
            if (event.key !== 'Escape') return;
            if (!successSheet.classList.contains('hidden')) closeSuccess();
            else if (!cartSheet.classList.contains('hidden')) closeCart();
            else if (!itemSheet.classList.contains('hidden')) closeItem();
        });
    }

    function openItem(id) {
        const item = items.get(String(id));
        if (!item) return;
        state.currentItem = item;

        itemContent.innerHTML =
            '<div class="overflow-hidden rounded-3xl border border-white/8 bg-white/[0.025]">' +
                (item.image
                    ? '<div class="grid aspect-[5/4] place-items-center overflow-hidden bg-[#211a20]"><img src="' + escapeHtml(item.image) + '" alt="' + escapeHtml(item.name) + '" class="size-full object-contain p-[6%]"></div>'
                    : '<div class="grid aspect-[16/9] place-items-center bg-[radial-gradient(circle_at_30%_20%,rgba(244,114,182,.16),transparent_36%),linear-gradient(135deg,#2b211d,#171311)]"><span class="text-6xl font-bold text-white/10">گ</span></div>') +
                '<div class="p-5"><div class="flex items-start justify-between gap-4"><div>' +
                    '<h2 id="sheet-title" class="text-2xl font-bold text-white">' + escapeHtml(item.name) + '</h2>' +
                    (item.description ? '<p class="mt-2 text-sm leading-7 text-stone-400">' + escapeHtml(item.description) + '</p>' : '') +
                '</div><span data-live-price class="shrink-0 rounded-xl bg-rose-100 px-3 py-2 text-sm font-bold text-stone-950">' +
                    formatPrice(item.price) + ' ' + escapeHtml(root.dataset.currency) +
                '</span></div></div></div>' +
            renderVariants(item) +
            renderOptions(item) +
            '<div class="mt-5 rounded-3xl border border-white/8 bg-white/[0.025] p-4">' +
                '<label class="text-sm font-medium text-stone-200">یادداشت این آیتم</label>' +
                '<textarea data-line-note rows="2" maxlength="300" placeholder="مثلاً کم‌شیرین یا بدون یخ..." class="mt-3 w-full resize-none rounded-2xl border border-white/10 bg-black/10 px-4 py-3 text-sm text-white outline-none placeholder:text-stone-600 focus:border-rose-200/35"></textarea>' +
            '</div>' +
            '<div class="mt-5 flex items-center justify-between gap-4">' +
                '<div class="inline-flex items-center rounded-2xl border border-white/10 bg-white/[0.03] p-1">' +
                    '<button type="button" data-qty-minus class="grid size-11 place-items-center rounded-xl text-xl text-white">−</button>' +
                    '<span data-qty class="grid min-w-12 place-items-center text-sm font-bold text-white">۱</span>' +
                    '<button type="button" data-qty-plus class="grid size-11 place-items-center rounded-xl text-xl text-white">+</button>' +
                '</div>' +
                '<button type="button" data-add-item class="flex h-13 flex-1 items-center justify-center gap-2 rounded-2xl bg-rose-100 px-5 text-sm font-bold text-stone-950 hover:bg-white">' +
                    'افزودن به سبد <span data-live-total>' + formatPrice(item.price) + ' ' + escapeHtml(root.dataset.currency) + '</span>' +
                '</button>' +
            '</div>' +
            '<div data-sheet-error class="mt-3 hidden rounded-2xl border border-red-300/15 bg-red-400/5 px-4 py-3 text-sm leading-6 text-red-200"></div>';

        bindItemForm(item);
        show(itemSheet);
    }

    function renderVariants(item) {
        if (!item.variants?.length) return '';
        return '<fieldset class="mt-5 rounded-3xl border border-white/8 bg-white/[0.025] p-4">' +
            '<legend class="px-1 text-sm font-semibold text-white">انتخاب اندازه</legend><div class="mt-3 grid gap-2 sm:grid-cols-2">' +
            item.variants.map((variant, index) =>
                '<label class="flex cursor-pointer items-center justify-between gap-3 rounded-2xl border border-white/10 bg-black/10 px-4 py-3">' +
                    '<span><span class="block text-sm font-medium text-white">' + escapeHtml(variant.name) + '</span><span class="mt-0.5 block text-xs text-stone-500">' +
                    formatPrice(variant.price) + ' ' + escapeHtml(root.dataset.currency) + '</span></span>' +
                    '<input type="radio" name="menu-variant" value="' + variant.id + '" ' + (index === 0 ? 'checked' : '') + ' class="size-4 accent-rose-200">' +
                '</label>'
            ).join('') + '</div></fieldset>';
    }

    function renderOptions(item) {
        return (item.options || []).map(option => {
            if (!option.values?.length) return '';
            const rule = option.required || option.min > 0 ? 'حداقل ' + toFa(Math.max(1, option.min || 0)) + ' انتخاب' : 'اختیاری';
            return '<fieldset class="mt-5 rounded-3xl border border-white/8 bg-white/[0.025] p-4" data-option-group data-option-min="' + option.min + '" data-option-max="' + option.max + '" data-option-required="' + (option.required ? '1' : '0') + '">' +
                '<legend class="flex w-full items-center justify-between gap-3 px-1 text-sm font-semibold text-white"><span>' + escapeHtml(option.name) + '</span><span class="text-xs font-normal text-stone-500">' + rule + '</span></legend>' +
                '<div class="mt-3 space-y-2">' +
                option.values.map(value =>
                    '<label class="flex cursor-pointer items-center justify-between gap-3 rounded-2xl border border-white/10 bg-black/10 px-4 py-3">' +
                        '<span class="flex min-w-0 items-center gap-3"><input type="' + (option.max === 1 ? 'radio' : 'checkbox') + '" name="menu-option-' + option.id + '" value="' + value.id + '" data-option-value class="size-4 shrink-0 accent-rose-200">' +
                        '<span class="truncate text-sm text-stone-200">' + escapeHtml(value.name) + '</span></span>' +
                        '<span class="shrink-0 text-xs text-stone-500">' + priceDeltaLabel(value.priceDelta) + '</span>' +
                    '</label>'
                ).join('') + '</div></fieldset>';
        }).join('');
    }

    function bindItemForm(item) {
        let quantity = 1;
        const refresh = () => {
            const selection = selectionFromForm();
            const price = resolvePrice(item, selection.variantId, selection.optionValueIds);
            $('[data-live-price]').textContent = formatPrice(price) + ' ' + root.dataset.currency;
            $('[data-live-total]').textContent = formatPrice(price * quantity) + ' ' + root.dataset.currency;
            $('[data-qty]').textContent = toFa(quantity);
        };

        all('#sheet-content input').forEach(input => input.addEventListener('change', () => {
            if (input.matches('[data-option-value][type="checkbox"]')) {
                const group = input.closest('[data-option-group]');
                const max = Number(group?.dataset.optionMax || 0);
                if (max > 0 && group.querySelectorAll('[data-option-value]:checked').length > max) {
                    input.checked = false;
                    toast('بیشتر از حد مجاز نمی‌توانی انتخاب کنی.');
                }
            }
            refresh();
        }));

        $('[data-qty-minus]')?.addEventListener('click', () => { quantity = Math.max(1, quantity - 1); refresh(); });
        $('[data-qty-plus]')?.addEventListener('click', () => { quantity = Math.min(1000, quantity + 1); refresh(); });

        $('[data-add-item]')?.addEventListener('click', () => {
            const selection = selectionFromForm();
            const errors = validateOptions(item, selection.optionValueIds);
            if (errors.length) {
                const box = $('[data-sheet-error]');
                box.textContent = errors[0];
                box.classList.remove('hidden');
                return;
            }

            const note = $('[data-line-note]')?.value.trim() || '';
            const line = {
                signature: makeSignature(item.id, selection.variantId, selection.optionValueIds, note),
                menu_item_id: item.id,
                menu_item_variant_id: selection.variantId,
                option_value_ids: selection.optionValueIds,
                quantity: quantity,
                note: note,
                name: item.name,
                variant_name: selection.variantId ? (item.variants.find(v => String(v.id) === String(selection.variantId))?.name || null) : null,
                unit_price: resolvePrice(item, selection.variantId, selection.optionValueIds),
            };

            const existing = state.cart.find(entry => entry.signature === line.signature);
            if (existing) existing.quantity = Math.min(1000, existing.quantity + quantity);
            else state.cart.push(line);

            saveCart();
            renderCart();
            closeItem();
            toast('به سبد سفارش اضافه شد.');
        });

        refresh();
    }

    function selectionFromForm() {
        const variant = itemContent.querySelector('input[name="menu-variant"]:checked');
        return {
            variantId: variant ? Number(variant.value) : null,
            optionValueIds: [...itemContent.querySelectorAll('[data-option-value]:checked')].map(input => Number(input.value)),
        };
    }

    function validateOptions(item, selectedIds) {
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
        const variant = variantId ? item.variants.find(v => String(v.id) === String(variantId)) : null;
        const base = Number(variant?.price ?? item.price ?? 0);
        const chosen = new Set(optionIds.map(Number));
        const delta = (item.options || []).reduce((sum, option) => sum + option.values
            .filter(value => chosen.has(Number(value.id)))
            .reduce((inner, value) => inner + Number(value.priceDelta || 0), 0), 0);
        return Math.max(0, base + delta);
    }

    function renderCart() {
        const count = state.cart.reduce((sum, line) => sum + Number(line.quantity), 0);
        const total = state.cart.reduce((sum, line) => sum + Number(line.unit_price) * Number(line.quantity), 0);
        cartCount.textContent = toFa(count);
        cartTotal.textContent = formatPrice(total);
        cartShell.classList.toggle('opacity-0', count === 0);
        cartShell.classList.toggle('translate-y-4', count === 0);
        cartItems.innerHTML = state.cart.map((line, index) =>
            '<article class="rounded-2xl border border-white/8 bg-white/[0.025] p-4"><div class="flex items-start justify-between gap-4">' +
                '<div class="min-w-0"><h3 class="truncate text-sm font-semibold text-white">' + escapeHtml(line.name) + '</h3>' +
                (line.variant_name ? '<p class="mt-1 text-xs text-stone-500">' + escapeHtml(line.variant_name) + '</p>' : '') +
                (line.note ? '<p class="mt-1 text-xs text-stone-500">' + escapeHtml(line.note) + '</p>' : '') +
                '</div><button type="button" data-remove="' + index + '" class="grid size-9 shrink-0 place-items-center rounded-xl bg-white/5 text-stone-400 hover:text-red-200" aria-label="حذف">×</button></div>' +
                '<div class="mt-4 flex items-center justify-between gap-4"><div class="inline-flex items-center rounded-xl border border-white/10 bg-black/10 p-0.5">' +
                    '<button type="button" data-plus="' + index + '" class="grid size-8 place-items-center rounded-lg text-white">+</button><span class="grid min-w-9 place-items-center text-xs font-bold text-white">' + toFa(line.quantity) + '</span><button type="button" data-minus="' + index + '" class="grid size-8 place-items-center rounded-lg text-white">−</button>' +
                '</div><span class="text-sm font-bold text-rose-100">' + formatPrice(line.unit_price * line.quantity) + ' ' + escapeHtml(root.dataset.currency) + '</span></div></article>'
        ).join('');

        cartEmpty.classList.toggle('hidden', state.cart.length > 0);
        submitButton.disabled = !state.cart.length || state.submitting;

        all('[data-remove]').forEach(button => button.addEventListener('click', () => { state.cart.splice(Number(button.dataset.remove), 1); saveCart(); renderCart(); }));
        all('[data-plus]').forEach(button => button.addEventListener('click', () => { const line = state.cart[Number(button.dataset.plus)]; if (line) line.quantity = Math.min(1000, line.quantity + 1); saveCart(); renderCart(); }));
        all('[data-minus]').forEach(button => button.addEventListener('click', () => {
            const index = Number(button.dataset.minus), line = state.cart[index];
            if (!line) return;
            line.quantity -= 1;
            if (line.quantity <= 0) state.cart.splice(index, 1);
            saveCart(); renderCart();
        }));
    }

    async function submitOrder() {
        if (state.submitting || !state.cart.length) return;

        state.submitting = true;
        state.checkoutKey ||= (globalThis.crypto?.randomUUID?.() || Date.now() + '-' + Math.random().toString(16).slice(2));
        $('[data-submit-label]').textContent = 'در حال ثبت...';
        $('[data-submit-spinner]').classList.remove('hidden');
        submitButton.disabled = true;
        alertBox.classList.add('hidden');

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
                    payment_method: document.querySelector('input[name="payment-method"]:checked')?.value || 'online',
                    items: state.cart.map(line => ({
                        menu_item_id: line.menu_item_id,
                        menu_item_variant_id: line.menu_item_variant_id,
                        quantity: line.quantity,
                        option_value_ids: line.option_value_ids,
                        note: line.note || null,
                    })),
                }),
            });

            const data = await response.json().catch(() => ({}));
            if (!response.ok) throw new Error(firstError(data) || data.message || 'ثبت سفارش انجام نشد.');

            state.cart = [];
            state.checkoutKey = null;
            saveCart();
            renderCart();
            $('#customer-note').value = '';
            closeCart();
            $('#success-order-number').textContent = data?.data?.order_number || '—';
            $('#success-total').textContent = formatPrice(data?.data?.total || 0);
            if (successTrackLink && data?.data?.public_token) {
                successTrackLink.href = '/orders/' + encodeURIComponent(data.data.public_token);
            }
            show(successSheet);
        } catch (error) {
            alertBox.textContent = error.message || 'خطایی رخ داد. دوباره تلاش کن.';
            alertBox.classList.remove('hidden');
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
            const hit = !query || (card.dataset.search || '').toLocaleLowerCase('fa-IR').includes(query);
            card.classList.toggle('hidden', !hit);
            if (hit) visible += 1;
        });
        all('[data-category-section]').forEach(section => {
            const hasVisible = [...section.querySelectorAll('[data-menu-card]')].some(card => !card.classList.contains('hidden'));
            section.classList.toggle('hidden', Boolean(query) && !hasVisible);
        });
        $('#empty-search').classList.toggle('hidden', Boolean(visible) || !query);
    }

    function activateCategory(id) {
        all('[data-category]').forEach(button => {
            const active = String(button.dataset.category) === String(id);
            button.classList.toggle('bg-rose-100', active);
            button.classList.toggle('text-stone-950', active);
            button.classList.toggle('border-rose-200/25', active);
            button.classList.toggle('bg-white/5', !active);
            button.classList.toggle('text-stone-300', !active);
        });
    }

    function openCart() { renderCart(); show(cartSheet); }
    function closeCart() { hide(cartSheet); }
    function closeItem() { hide(itemSheet); state.currentItem = null; }
    function closeSuccess() { hide(successSheet); }

    function show(node) {
        node.classList.remove('hidden');
        node.classList.add('flex');
        node.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-hidden');
    }

    function hide(node) {
        node.classList.add('hidden');
        node.classList.remove('flex');
        node.setAttribute('aria-hidden', 'true');
        if ([itemSheet, cartSheet, successSheet].every(panel => panel.classList.contains('hidden'))) {
            document.body.classList.remove('overflow-hidden');
        }
    }

    function saveCart() { localStorage.setItem(cartKey, JSON.stringify(state.cart)); }
    function loadCart() {
        try {
            const data = JSON.parse(localStorage.getItem(cartKey) || '[]');
            if (!Array.isArray(data)) return [];

            return data.map(line => {
                const item = items.get(String(line?.menu_item_id));
                if (!item) return null;

                const variantId = line.menu_item_variant_id
                    ? Number(line.menu_item_variant_id)
                    : null;
                if (variantId && !item.variants.some(variant => Number(variant.id) === variantId)) return null;

                const optionIds = [...new Set(Array.isArray(line.option_value_ids) ? line.option_value_ids.map(Number) : [])]
                    .filter(id => item.options.some(option => option.values.some(value => Number(value.id) === id)));
                if (validateOptions(item, optionIds).length) return null;

                const quantity = Math.min(1000, Math.max(1, Number(line.quantity) || 1));
                const note = typeof line.note === 'string' ? line.note.slice(0, 300) : '';
                const unitPrice = resolvePrice(item, variantId, optionIds);
                const variantName = variantId
                    ? (item.variants.find(variant => Number(variant.id) === variantId)?.name || null)
                    : null;

                return {
                    signature: makeSignature(item.id, variantId, optionIds, note),
                    menu_item_id: item.id,
                    menu_item_variant_id: variantId,
                    option_value_ids: optionIds,
                    quantity,
                    note,
                    name: item.name,
                    variant_name: variantName,
                    unit_price: unitPrice,
                };
            }).filter(Boolean);
        } catch (_) {
            return [];
        }
    }

    let toastTimer;
    function toast(message) {
        const box = $('#toast');
        $('[data-toast-message]').textContent = message;
        box.classList.remove('opacity-0');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => box.classList.add('opacity-0'), 2200);
    }

    function firstError(data) {
        const values = Object.values(data?.errors || {});
        const first = values.find(value => Array.isArray(value) && value.length);
        return first?.[0] || null;
    }

    function makeSignature(itemId, variantId, optionIds, note) {
        return [itemId, variantId || '', [...optionIds].sort((a, b) => a - b).join(','), note].join('|');
    }

    function priceDeltaLabel(value) {
        value = Number(value || 0);
        return value > 0 ? '+' + formatPrice(value) : value < 0 ? formatPrice(value) : 'بدون تغییر';
    }

    function formatPrice(value) { return new Intl.NumberFormat('fa-IR').format(Math.round(Number(value) || 0)); }
    function toFa(value) { return String(value).replace(/\d/g, digit => '۰۱۲۳۴۵۶۷۸۹'[digit]); }
    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, char => ({ '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#039;' }[char]));
    }
})();