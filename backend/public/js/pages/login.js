// Form login dikirim ke /login lewat POST Blade + CSRF.
// Tidak ada fetch /api/login, token, atau pengecekan role di browser.

export function initLogin() {
    document.getElementById('email')?.focus();
}
