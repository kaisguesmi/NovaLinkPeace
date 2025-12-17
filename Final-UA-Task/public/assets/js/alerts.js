// Unified alert/toast renderer for PeaceLink
(() => {
    const STACK_ID = 'toast-stack';
    const MAX_TOASTS = 4;

    const stack = ensureStack();

    function ensureStack() {
        let el = document.getElementById(STACK_ID);
        if (el) return el;
        el = document.createElement('div');
        el.id = STACK_ID;
        el.className = 'toast-stack';
        document.body.appendChild(el);
        return el;
    }

    function inferType(message) {
        const text = (message || '').toString().toLowerCase();
        if (/erreur|error|fail|echec|échec|impossible|refus|denied|introuvable/.test(text)) return 'danger';
        if (/succ[eè]s|ok|bravo|valid|enregistr|ajout[eé]|envoy[eé]/.test(text)) return 'success';
        if (/warn|attention|avert|risque|caution/.test(text)) return 'warning';
        return 'info';
    }

    function dismiss(toast) {
        if (!toast) return;
        const timeoutId = toast.dataset.timeout ? parseInt(toast.dataset.timeout, 10) : null;
        if (timeoutId) {
            clearTimeout(timeoutId);
        }
        toast.classList.remove('is-visible');
        toast.classList.add('is-hiding');
        setTimeout(() => toast.remove(), 220);
    }

    function showToast(message, type = null, opts = {}) {
        if (message == null) return;
        const text = String(message);
        const toastType = type || inferType(text);

        while (stack.children.length >= (opts.max || MAX_TOASTS)) {
            stack.removeChild(stack.firstElementChild);
        }

        const toast = document.createElement('div');
        toast.className = `toast toast-${toastType}`;
        toast.setAttribute('role', 'status');

        const dot = document.createElement('span');
        dot.className = 'toast-dot';
        toast.appendChild(dot);

        const content = document.createElement('div');
        content.className = 'toast-content';
        content.textContent = text;
        toast.appendChild(content);

        const close = document.createElement('button');
        close.type = 'button';
        close.className = 'toast-close';
        close.innerHTML = '&times;';
        close.addEventListener('click', () => dismiss(toast));
        toast.appendChild(close);

        stack.appendChild(toast);
        requestAnimationFrame(() => toast.classList.add('is-visible'));

        const duration = typeof opts.duration === 'number' ? opts.duration : 6000;
        if (duration > 0) {
            const timeoutId = setTimeout(() => dismiss(toast), duration);
            toast.dataset.timeout = timeoutId.toString();
        }

        return toast;
    }

    window.showToast = (message, type, opts) => showToast(message, type, opts);
    window.showFlash = window.showToast;

    const nativeAlert = window.alert.bind(window);
    window.alert = function patchedAlert(message) {
        showToast(message, inferType(message), { duration: 7000 });
        // Keep a console trace for debugging if needed
        console.warn('[alert]', message);
        // Do not block the UI; if blocking is required, fallback to native
        // nativeAlert(message);
    };

})();
