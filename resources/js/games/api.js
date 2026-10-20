// JSON calls to the game endpoints on the web session (CSRF token from the page's meta tag).
// Errors come back as GameApiError with the server's translated message and stable code.

export class GameApiError extends Error {
    constructor(message, { status = 0, code = null, errors = null, retryAfter = 0 } = {}) {
        super(message);
        this.status = status;
        this.code = code;
        this.errors = errors;
        this.retryAfter = retryAfter;
    }
}

const csrf = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

export async function call(url, { method = 'GET', body = null, i18n = {} } = {}) {
    let response;

    try {
        response = await fetch(url, {
            method,
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                ...(body ? { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf() } : { 'X-CSRF-TOKEN': csrf() }),
            },
            body: body ? JSON.stringify(body) : null,
        });
    } catch (e) {
        throw new GameApiError(i18n.network || 'Network error', { status: 0, code: 'network' });
    }

    let data = null;
    try {
        data = await response.json();
    } catch (e) {
        data = null;
    }

    if (!response.ok) {
        const firstError = data?.errors ? Object.values(data.errors)[0]?.[0] : null;
        throw new GameApiError(data?.message && response.status !== 500 ? (firstError || data.message) : (i18n.generic || 'Error'), {
            status: response.status,
            code: data?.error || (response.status === 419 ? 'session' : null),
            errors: data?.errors || null,
            retryAfter: data?.retry_after || 0,
        });
    }

    return data;
}

/** A stable-ish device signature for fraud checks (the server hashes it with the user agent). */
export function deviceSignature() {
    let id = '';
    try {
        id = localStorage.getItem('games.device') || '';
        if (!id) {
            id = Math.random().toString(36).slice(2) + Date.now().toString(36);
            localStorage.setItem('games.device', id);
        }
    } catch (e) {
        id = 'no-storage';
    }

    let tz = '';
    try {
        tz = Intl.DateTimeFormat().resolvedOptions().timeZone || '';
    } catch (e) {
        tz = '';
    }

    return [
        id,
        `${screen.width}x${screen.height}x${window.devicePixelRatio || 1}`,
        tz,
        navigator.language || '',
        navigator.hardwareConcurrency || 0,
        navigator.maxTouchPoints || 0,
    ].join('|');
}
