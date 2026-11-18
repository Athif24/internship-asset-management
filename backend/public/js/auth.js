// Web Admin TIDAK menyimpan token/user di localStorage.
// Sesi Laravel tanpa remember-me menangani autentikasi; profil dirender Blade.
// File ini hanya menyediakan pembaca CSRF token dari meta layout.

export function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content || '';
}
