// Helper tampilan: escape, format, badge, state error, toast, modal, dan refresh diam-diam.

export function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, (char) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;',
    }[char]));
}

export function formatDate(value) {
    if (!value) return '—';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return escapeHtml(value);
    return new Intl.DateTimeFormat('id-ID', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(date);
}

export function statusBadge(status) {
    const map = {
        AVAILABLE: ['badge-success', 'AVAILABLE'],
        BORROWED: ['badge-warning', 'BORROWED'],
        MAINTENANCE: ['badge-secondary', 'MAINTENANCE'],
        RETURNED: ['badge-success', 'RETURNED'],
    };
    const [classes, label] = map[status] || ['badge-secondary', status || '-'];
    return `<span class="badge ${classes}">${escapeHtml(label)}</span>`;
}

export function formatLoanDate(value) {
    if (!value) return '-';

    // API mengirim datetime UTC (mis. 2026-11-17T09:10:00.000000Z).
    // Ambil komponen tanggalnya apa adanya agar tidak bergeser mengikuti timezone browser.
    const match = String(value).match(/^(\d{4})-(\d{2})-(\d{2})[T ](\d{2}):(\d{2})/);
    if (!match) return escapeHtml(value);

    const [, year, monthNumber, day, hour, minute] = match;
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    const month = months[Number(monthNumber) - 1] || monthNumber;

    return `${Number(day)} ${month} ${year}, ${hour}:${minute}`;
}

export function formatPrice(value) {
    if (value === null || value === undefined || value === '') return '-';
    const amount = Number(value);
    if (!Number.isFinite(amount)) return '-';

    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(amount);
}

export function friendlyError(error) {
    const map = {
        'AST-01': 'Kode aset sudah digunakan (AST-01, HTTP 409).',
        'AST-03': 'Aset sedang dipinjam.',
        'AST-04': 'Aset sedang dalam maintenance.',
        'AST-06': 'Aset tidak sedang dipinjam.',
        'AST-07': 'Hanya peminjam yang boleh mengembalikan aset.',
        'AST-09': 'Sistem sedang sibuk. Coba lagi.',
        'AST-10': 'Aset sedang dipinjam, status tidak dapat diubah (AST-10).',
        CATEGORY_HAS_ASSETS: 'Kategori masih memiliki aset dan tidak dapat dihapus.',
        ASSET_STILL_BORROWED: 'Aset sedang dipinjam dan tidak dapat dihapus.',
        ASSET_HAS_HISTORY: 'Aset memiliki riwayat pinjaman dan tidak dapat dihapus.',
        FK_CONSTRAINT: 'Data masih digunakan oleh data lain.',
        TIMEOUT: 'Server tidak merespons. Coba lagi.',
        VALIDATION_ERROR: 'Periksa kembali input.',
        INVALID_CREDENTIALS: 'Email atau password salah.',
        UNAUTHENTICATED: 'Belum login. Silakan masuk kembali.',
        NETWORK_ERROR: 'Tidak dapat terhubung ke server.',
        FORBIDDEN: 'Akun Anda tidak memiliki akses ke halaman ini.',
        NOT_FOUND: 'Data tidak ditemukan.',
        INTERNAL_ERROR: 'Terjadi kesalahan server.',
    };
    return map[error?.code] || error?.message || 'Terjadi kesalahan.';
}

export function toast(message, type = 'success') {
    let host = document.getElementById('toast-host');
    if (!host) {
        host = document.createElement('div');
        host.id = 'toast-host';
        host.className = 'toast-host';
        document.body.appendChild(host);
    }
    const element = document.createElement('div');
    element.className = `toast toast-${type}`;
    element.textContent = message;
    host.appendChild(element);
    window.setTimeout(() => element.remove(), 4000);
}

export function loadingRow(colspan, text = 'Memuat data...') {
    return `<tr><td colspan="${colspan}" class="loading-state">${escapeHtml(text)}</td></tr>`;
}

export function emptyRow(colspan, text = 'Belum ada data.') {
    return `<tr><td colspan="${colspan}" class="empty-state"><strong>Tidak ada data</strong>${escapeHtml(text)}</td></tr>`;
}

/* -------------------------------------------------------------------------- */
/* State ERROR, EMPTY, dan dropdown gagal                                     */
/* -------------------------------------------------------------------------- */

/** Tampilkan .alert-error + tombol Coba lagi di dalam container kartu. */
export function errorState(container, message = 'Gagal memuat data.', onRetry = null) {
    if (!container) return;
    container.hidden = false;
    container.innerHTML = `
        <div class="alert-error">
            <span>${escapeHtml(message)}</span>
            ${onRetry ? '<button type="button" class="btn sec btn-sm" data-error-retry>Coba lagi</button>' : ''}
        </div>`;

    if (onRetry) {
        container.querySelector('[data-error-retry]')?.addEventListener('click', onRetry);
    }
}

export function clearErrorState(container) {
    if (!container) return;
    container.hidden = true;
    container.innerHTML = '';
}

/** Dropdown gagal: opsi "Gagal memuat" + tombol retry di dekatnya. */
export function selectError(select, onRetry = null) {
    if (!select) return;
    select.innerHTML = '<option value="" disabled selected>Gagal memuat</option>';
    select.disabled = true;

    const group = select.closest('.filter-group, .form-group') || select.parentElement;
    if (!group) return;

    let button = group.querySelector('.select-retry');
    if (!button) {
        button = document.createElement('button');
        button.type = 'button';
        button.className = 'btn sec btn-sm select-retry';
        button.textContent = 'Coba lagi';
        group.appendChild(button);
    }
    button.onclick = () => {
        if (onRetry) onRetry();
    };
}

export function clearSelectError(select) {
    if (!select) return;
    select.disabled = false;
    const group = select.closest('.filter-group, .form-group') || select.parentElement;
    group?.querySelector('.select-retry')?.remove();
}

/* -------------------------------------------------------------------------- */
/* Panel error halaman penuh (sidebar tetap)                                  */
/* -------------------------------------------------------------------------- */

export function isPageLevelError(error) {
    const status = Number(error?.status || 0);
    return status === 403 || status === 404 || status >= 500;
}

export function showPageError(container, error, onRetry = null) {
    if (!container) return;

    let title = 'Terjadi kesalahan server';
    let message = friendlyError(error);
    let action = '<button type="button" class="btn sec" data-page-retry>Coba lagi</button>';

    if (error?.status === 403) {
        title = 'Akses ditolak';
        message = 'Akun Anda tidak memiliki akses ke halaman ini.';
        action = '';
    } else if (error?.status === 404) {
        title = 'Data tidak ditemukan';
        message = 'Data yang diminta tidak tersedia.';
        action = '<button type="button" class="btn sec" data-page-back>Kembali</button>';
    }

    container.innerHTML = `
        <div class="card page-error-panel">
            <h1>${escapeHtml(title)}</h1>
            <p class="cell-muted">${escapeHtml(message)}</p>
            ${action}
        </div>`;

    container.querySelector('[data-page-back]')?.addEventListener('click', () => window.history.back());
    container.querySelector('[data-page-retry]')?.addEventListener('click', () => {
        if (onRetry) onRetry();
        else window.location.reload();
    });
}

/* -------------------------------------------------------------------------- */
/* Error validasi per field                                                   */
/* -------------------------------------------------------------------------- */

export function clearFieldError(modal, name) {
    if (!modal || !name) return;
    modal.querySelector(`[data-field-error="${name}"]`)?.remove();
    modal.querySelector(`[name="${name}"]`)?.classList.remove('invalid');
}

export function clearAllFieldErrors(modal) {
    if (!modal) return;
    modal.querySelectorAll('.field-error').forEach((node) => node.remove());
    modal.querySelectorAll('.form-control.invalid').forEach((node) => node.classList.remove('invalid'));
}

export function showFieldErrors(modal, detail) {
    if (!modal || !detail || typeof detail !== 'object') return;

    Object.entries(detail).forEach(([name, messages]) => {
        const field = modal.querySelector(`[name="${name}"]`);
        if (!field) return;

        clearFieldError(modal, name);
        field.classList.add('invalid');

        const text = Array.isArray(messages) ? messages.join(' ') : String(messages);
        const node = document.createElement('div');
        node.className = 'field-error';
        node.dataset.fieldError = name;
        node.textContent = text;
        field.insertAdjacentElement('afterend', node);
    });
}

/* -------------------------------------------------------------------------- */
/* Refresh diam-diam saat tab aktif kembali                                   */
/* -------------------------------------------------------------------------- */

let silentRefreshHandler = null;
let silentRefreshRunning = false;

export function registerSilentRefresh(handler) {
    silentRefreshHandler = handler;
}

export function runSilentRefresh() {
    if (document.visibilityState !== 'visible') return;
    // Jangan timpa form yang sedang dibuka (edit/detail).
    if (document.querySelector('.modal-overlay')) return;
    // Cegah request bertumpuk bila server lebih lambat dari interval.
    if (silentRefreshRunning) return;
    if (typeof silentRefreshHandler !== 'function') return;

    silentRefreshRunning = true;
    Promise.resolve(silentRefreshHandler())
        .catch(() => {
            // Error sudah ditangani masing-masing halaman.
        })
        .finally(() => {
            silentRefreshRunning = false;
        });
}

/* -------------------------------------------------------------------------- */
/* Modal generik                                                              */
/* -------------------------------------------------------------------------- */

/**
 * Modal generik. onConfirm(modalElement) boleh return Promise.
 * Resolve dengan nilai return onConfirm, atau null jika dibatalkan.
 */
export function openModal({
    title,
    contentHtml = '',
    confirmLabel = 'Simpan',
    confirmClass = '',
    onConfirm,
    extraFooter = '',
    onExtra = null,
    onReady = null,
}) {
    return new Promise((resolve) => {
        const overlay = document.createElement('div');
        overlay.className = 'modal-overlay';
        overlay.innerHTML = `
            <div class="modal" role="dialog" aria-modal="true">
                <div class="modal-header">
                    <h3>${escapeHtml(title)}</h3>
                    <button type="button" class="modal-close" data-cancel aria-label="Tutup">&times;</button>
                </div>
                <div class="modal-body">${contentHtml}</div>
                <div class="modal-footer">
                    ${extraFooter}
                    <button type="button" class="btn btn-light" data-cancel>Batal</button>
                    <button type="button" class="btn ${confirmClass || 'btn-primary'}" data-confirm>${escapeHtml(confirmLabel)}</button>
                </div>
            </div>`;
        document.body.appendChild(overlay);

        const close = (value) => {
            overlay.remove();
            resolve(value);
        };

        overlay.querySelectorAll('[data-cancel]').forEach((button) => {
            button.addEventListener('click', () => close(null));
        });
        overlay.addEventListener('click', (event) => {
            if (event.target === overlay) close(null);
        });

        const errorBox = overlay.querySelector('#form-error');
        let retryAction = null;

        const clearError = () => {
            clearAllFieldErrors(overlay);
            if (!errorBox) return;
            errorBox.textContent = '';
            errorBox.hidden = true;
        };

        const showError = (error) => {
            const isValidation = error?.code === 'VALIDATION_ERROR' && error?.detail;
            const message = isValidation ? 'Periksa kembali input.' : friendlyError(error);
            const retryable = ['TIMEOUT', 'AST-09'].includes(error?.code);

            if (isValidation) showFieldErrors(overlay, error.detail);

            if (!errorBox) {
                // Toast hanya untuk sukses. Error tetap tampil di panel inline.
                const fallback = overlay.querySelector('.modal-body');
                if (fallback) {
                    const box = document.createElement('div');
                    box.className = 'alert-error';
                    box.textContent = message;
                    fallback.prepend(box);
                }
                return;
            }

            errorBox.innerHTML = `
                <span>${escapeHtml(message)}</span>
                ${retryable ? '<button type="button" class="btn sec btn-sm" data-error-retry>Coba lagi</button>' : ''}`;
            errorBox.hidden = false;

            if (retryable && retryAction) {
                errorBox.querySelector('[data-error-retry]')?.addEventListener('click', () => retryAction());
            }
        };

        overlay.querySelectorAll('input[name], select[name], textarea[name]').forEach((field) => {
            const clear = () => clearFieldError(overlay, field.name);
            field.addEventListener('input', clear);
            field.addEventListener('change', clear);
        });

        const confirm = overlay.querySelector('[data-confirm]');
        confirm.addEventListener('click', async () => {
            clearError();
            retryAction = () => confirm.click();
            confirm.disabled = true;
            const original = confirm.textContent;
            confirm.textContent = 'Memproses...';
            try {
                const result = await onConfirm(overlay);
                close(result === undefined ? true : result);
            } catch (error) {
                showError(error);
                confirm.disabled = false;
                confirm.textContent = original;
            }
        });

        const extra = overlay.querySelector('[data-extra]');
        if (extra && onExtra) {
            extra.addEventListener('click', async () => {
                clearError();
                retryAction = () => extra.click();
                extra.disabled = true;
                const original = extra.textContent;
                extra.textContent = 'Memproses...';
                try {
                    const result = await onExtra(overlay);
                    if (result) close(result);
                } catch (error) {
                    showError(error);
                } finally {
                    extra.disabled = false;
                    extra.textContent = original;
                }
            });
        }

        const firstField = overlay.querySelector('input, select, textarea');
        if (firstField) firstField.focus();
        if (onReady) onReady(overlay);
    });
}

export function confirmDialog(message, confirmLabel = 'Hapus') {
    return openModal({
        title: 'Konfirmasi',
        contentHtml: `<p class="confirm-text">${escapeHtml(message)}</p>`,
        confirmLabel,
        confirmClass: 'btn-danger',
        onConfirm: () => true,
    });
}

export function fieldValue(modal, name) {
    return modal.querySelector(`[name="${name}"]`)?.value.trim() ?? '';
}

export function options(list, selected = null, placeholder = '— Pilih —') {
    const items = (list || []).map((item) => {
        const value = item.id ?? item.value;
        const label = item.name ?? item.label;
        const active = String(value) === String(selected) ? ' selected' : '';
        return `<option value="${escapeHtml(value)}"${active}>${escapeHtml(label)}</option>`;
    }).join('');
    return `<option value="">${escapeHtml(placeholder)}</option>${items}`;
}
