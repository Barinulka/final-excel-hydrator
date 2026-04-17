const STORAGE_KEY = 'finalExcelHydrator.pendingToast';

export function showToast(message, type = 'success') {
    if (!message) {
        return;
    }

    const existingToast = document.querySelector('.app-toast');
    if (existingToast) {
        existingToast.remove();
    }

    const toast = document.createElement('div');
    toast.className = `app-toast app-toast--${type}`;
    toast.setAttribute('role', type === 'error' ? 'alert' : 'status');
    toast.textContent = message;

    document.body.append(toast);

    const delay = type === 'error' ? 5000 : 3000;
    window.setTimeout(() => {
        toast.classList.add('app-toast--hidden');
        window.setTimeout(() => toast.remove(), 250);
    }, delay);
}

export function storePendingToast(message, type = 'success') {
    if (!message) {
        return;
    }

    window.sessionStorage.setItem(STORAGE_KEY, JSON.stringify({ message, type }));
}

export function flushPendingToast() {
    const rawToast = window.sessionStorage.getItem(STORAGE_KEY);
    if (!rawToast) {
        return;
    }

    window.sessionStorage.removeItem(STORAGE_KEY);

    let toast = null;
    try {
        toast = JSON.parse(rawToast);
    } catch (error) {
        return;
    }

    showToast(toast?.message, toast?.type ?? 'success');
}
