(() => {
    const root = document.querySelector('#order-waiting');
    if (!root) return;

    const url = root.dataset.statusUrl;
    const label = root.querySelector('[data-status-label]');
    const miniLabel = root.querySelector('[data-mini-status]');
    const visualLabel = root.querySelector('[data-visual-status]');
    const badge = root.querySelector('#status-badge');
    const readyPanel = root.querySelector('[data-ready-panel]');
    const readyTitle = root.querySelector('[data-ready-title]');
    const readyCopy = root.querySelector('[data-ready-copy]');
    const steps = ['pending', 'confirmed', 'preparing', 'ready', 'served'];
    const terminal = new Set(['served', 'delivered', 'completed', 'cancelled']);
    let stopped = false;
    let inFlight = false;

    const labels = {
        pending: 'در صف بررسی',
        confirmed: 'سفارش تأیید شد',
        preparing: 'در حال آماده‌سازی',
        ready: 'آماده شد',
        served: 'سرو شد',
        out_for_delivery: 'در مسیر',
        delivered: 'تحویل شد',
        completed: 'تکمیل شد',
        cancelled: 'لغو شد',
    };

    update(root.dataset.initialStatus, label?.textContent?.trim() || labels[root.dataset.initialStatus] || 'در حال بررسی');

    async function refresh() {
        if (inFlight || stopped || document.visibilityState === 'hidden') return;
        inFlight = true;
        try {
            const response = await fetch(url, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                cache: 'no-store',
                credentials: 'same-origin',
            });
            if (!response.ok) return;
            const payload = await response.json();
            const data = payload?.data;
            if (!data?.status) return;
            update(data.status, data.status_label || labels[data.status] || 'در حال بررسی');
        } catch {
            // A temporary connection issue must not erase the last confirmed status.
        } finally {
            inFlight = false;
        }
    }

    function update(status, statusLabel) {
        const index = steps.indexOf(status);
        if (label) label.textContent = statusLabel;
        if (miniLabel) miniLabel.textContent = statusLabel;
        if (visualLabel) visualLabel.textContent = statusLabel;
        badge?.classList.toggle('is-cancelled', status === 'cancelled');
        root.querySelectorAll('[data-progress-step]').forEach((node) => {
            const stepIndex = steps.indexOf(node.dataset.progressStep);
            node.classList.toggle('is-done', stepIndex >= 0 && (index > stepIndex || terminal.has(status) && status !== 'cancelled'));
            node.classList.toggle('is-current', stepIndex >= 0 && stepIndex === index);
            if (status === 'cancelled') {
                node.classList.remove('is-current');
                node.classList.remove('is-done');
            }
        });
        root.querySelectorAll('[data-progress-line]').forEach((node) => {
            const stepIndex = steps.indexOf(node.dataset.progressLine);
            node.classList.toggle('is-done', stepIndex >= 0 && (index > stepIndex || terminal.has(status) && status !== 'cancelled'));
            if (status === 'cancelled') node.classList.remove('is-done');
        });
        if (readyPanel) readyPanel.hidden = !terminal.has(status);
        if (readyTitle) readyTitle.textContent = status === 'cancelled' ? 'این سفارش لغو شده است.' : status === 'delivered' || status === 'completed' || status === 'served' ? 'نوش جان ✨' : 'سفارش آماده شد ✨';
        if (readyCopy) readyCopy.textContent = status === 'cancelled'
            ? 'برای پیگیری بیشتر با رستوران تماس بگیر.'
            : status === 'served' || status === 'delivered' || status === 'completed'
                ? 'از همراهی‌ات ممنونیم؛ امیدواریم از تجربه‌ات لذت ببری.'
                : 'سفارش آماده است؛ از غذایت لذت ببر.';
        stopped = status === 'cancelled';
    }

    refresh();
    window.setInterval(refresh, 15000);
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') refresh();
    });
})();
