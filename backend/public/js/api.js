// Wrapper fetch untuk Web Admin (Blade + session Laravel).
// - Memakai cookie same-origin, tanpa Authorization Bearer.
// - CSRF token selalu dibaca dari meta (session bisa berganti/regenerate).
// - Mem-parsing envelope { success, message, data, error }.
// - Melempar Error dengan properti code/status/detail.

import { csrfToken } from './auth.js';

async function request(method, path, body = null) {
    const headers = { Accept: 'application/json' };

    if (!['GET', 'HEAD'].includes(method)) {
        headers['X-CSRF-TOKEN'] = csrfToken();
    }

    let payload = null;
    if (body !== null) {
        headers['Content-Type'] = 'application/json';
        payload = JSON.stringify(body);
    }

    const controller = new AbortController();
    const timeoutId = window.setTimeout(() => controller.abort(), 10000);

    let response;
    let json;

    try {
        response = await fetch(path, {
            method,
            headers,
            body: payload,
            credentials: 'same-origin',
            signal: controller.signal,
        });
        json = await response.json();
    } catch (e) {
        if (e?.name === 'AbortError') {
            const error = new Error('Server tidak merespons. Coba lagi.');
            error.code = 'TIMEOUT';
            error.status = 0;
            throw error;
        }

        const error = new Error('Tidak dapat terhubung ke server.');
        error.code = 'NETWORK_ERROR';
        error.status = 0;
        throw error;
    } finally {
        window.clearTimeout(timeoutId);
    }

    if (response.status === 401) {
        window.location.href = '/login';
        const error = new Error('Belum login.');
        error.code = 'UNAUTHENTICATED';
        error.status = 401;
        throw error;
    }

    if (!response.ok || json?.success === false) {
        const error = new Error(json?.message || 'Terjadi kesalahan.');
        error.code = json?.error?.code || 'ERROR';
        error.detail = json?.error?.detail || null;
        error.status = response.status;
        throw error;
    }

    return json.data;
}

export const api = {
    get: (path) => request('GET', path),
    post: (path, body) => request('POST', path, body),
    put: (path, body) => request('PUT', path, body),
    delete: (path) => request('DELETE', path),
};

// Susun query string, abaikan nilai kosong.
export function query(params = {}) {
    const search = new URLSearchParams();
    Object.entries(params).forEach(([key, value]) => {
        if (value !== '' && value !== null && value !== undefined) {
            search.append(key, value);
        }
    });
    const string = search.toString();
    return string ? `?${string}` : '';
}
