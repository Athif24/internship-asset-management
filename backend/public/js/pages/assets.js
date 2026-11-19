import { api } from '../api.js';
import {
    statusBadge, formatLoanDate, formatPrice, escapeHtml, emptyRow,
    toast, friendlyError, options, openModal, confirmDialog, fieldValue,
    errorState, clearErrorState, showPageError, isPageLevelError,
    registerSilentRefresh,
} from '../ui.js';

let assets = [];
let categories = [];
let categoriesLoadError = null;

function templateHtml(id) {
    return document.getElementById(id)?.innerHTML.trim() || '';
}

function cloneTemplate(id) {
    const holder = document.createElement('div');
    holder.innerHTML = templateHtml(id);
    return holder;
}

/** Data awal (#assets-body, kategori select) sudah dirender Blade. */
export function initAssets() {
    const root = document.getElementById('page-root');
    assets = parseData(root.dataset.assets, []);
    categories = parseData(root.dataset.categories, []);

    document.getElementById('btn-create')?.addEventListener('click', () => openForm());

    document.getElementById('assets-body')?.addEventListener('click', async (event) => {
        const nameButton = event.target.closest('button[data-detail]');
        if (nameButton) {
            await openDetail(nameButton.dataset.detail);
            return;
        }

        const button = event.target.closest('button[data-action="edit"]');
        if (!button) return;

        const asset = assets.find((item) => String(item.id) === String(button.dataset.id));
        if (asset) openForm(asset);
    });

    registerSilentRefresh(load);
}

function parseData(raw, fallback) {
    try {
        return raw ? JSON.parse(raw) : fallback;
    } catch (e) {
        return fallback;
    }
}

async function loadCategories() {
    const box = document.getElementById('asset-category-error');
    const activeSelect = document.querySelector('.modal #f-category');

    try {
        const data = await api.get('/categories/refresh');
        categories = data.categories || [];
        categoriesLoadError = null;
        clearErrorState(box);

        if (activeSelect) {
            activeSelect.disabled = false;
            activeSelect.innerHTML = options(
                categories, activeSelect.dataset.selected || null, 'Pilih kategori'
            );
            activeSelect.parentElement.querySelector('.select-retry')?.remove();
        }
    } catch (error) {
        categoriesLoadError = error;
        errorState(box, 'Gagal memuat daftar kategori.', loadCategories);

        if (activeSelect) {
            activeSelect.innerHTML = '<option value="" disabled selected>Gagal memuat</option>';
            activeSelect.disabled = true;
            const group = activeSelect.parentElement;
            let button = group.querySelector('.select-retry');
            if (!button) {
                button = document.createElement('button');
                button.type = 'button';
                button.className = 'btn sec btn-sm select-retry';
                button.textContent = 'Coba lagi';
                group.appendChild(button);
            }
            button.onclick = () => loadCategories();
        }
    }
}

async function load() {
    const errorBox = document.getElementById('assets-error');
    const root = document.getElementById('page-root');

    try {
        const data = await api.get('/assets/refresh');
        assets = data.assets || [];
        clearErrorState(errorBox);

        document.getElementById('assets-body').innerHTML = assets.length ? assets.map((asset) => `
            <tr>
                <td class="cell-code">${escapeHtml(asset.assets_code)}</td>
                <td>
                    <button type="button" class="name-link" data-detail="${asset.id}">
                        ${escapeHtml(asset.name)}
                    </button>
                </td>
                <td>${escapeHtml(asset.category?.name || '-')}</td>
                <td>${statusBadge(asset.status)}</td>
                <td class="cell-nowrap">${formatPrice(asset.purchase_price)}${asset.purchase_year ? ' · ' + escapeHtml(asset.purchase_year) : ''}</td>
                <td>
                    <button type="button" class="btn sec btn-sm" data-action="edit" data-id="${asset.id}">Edit</button>
                </td>
            </tr>`).join('') : emptyRow(6, 'Belum ada data.');
    } catch (error) {
        if (isPageLevelError(error)) {
            showPageError(root, error, () => window.location.reload());
            return;
        }

        document.getElementById('assets-body').innerHTML = '';
        errorState(errorBox, friendlyError(error), load);
    }
}

function markCategoryError(holder) {
    const select = holder.querySelector('#f-category');
    if (!select) return;
    select.innerHTML = '<option value="" disabled selected>Gagal memuat</option>';
    select.disabled = true;

    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'btn sec btn-sm select-retry';
    button.dataset.categoryRetry = '';
    button.textContent = 'Coba lagi';
    select.insertAdjacentElement('afterend', button);
}

function formContent(asset = null) {
    const holder = cloneTemplate('asset-form-template');
    const isEdit = !!asset;
    const borrowed = isEdit && asset.status === 'BORROWED';

    if (isEdit) {
        holder.querySelector('[name="assets_code"]').value = asset.assets_code || '';
        holder.querySelector('[name="name"]').value = asset.name || '';
        holder.querySelector('[name="purchase_price"]').value = asset.purchase_price ?? '';
        holder.querySelector('[name="purchase_year"]').value = asset.purchase_year ?? '';

        const category = holder.querySelector('[name="category_id"]');
        category.dataset.selected = asset.category_id || '';
        if (!categoriesLoadError) category.value = String(asset.category_id || '');
    }

    if (categoriesLoadError) markCategoryError(holder);

    if (borrowed) {
        holder.querySelector('#f-status-group').outerHTML = templateHtml('asset-status-locked-template');
    } else if (isEdit && asset.status === 'MAINTENANCE') {
        holder.querySelector('[name="status"]').value = 'MAINTENANCE';
    }

    return holder.innerHTML;
}

function openForm(asset = null) {
    const isEdit = !!asset;
    const borrowed = isEdit && asset.status === 'BORROWED';

    const extraFooter = isEdit
        ? '<button type="button" class="btn sec" data-extra>Hapus</button>'
        : '';

    openModal({
        title: isEdit ? 'Edit Aset' : 'Tambah Aset Baru',
        contentHtml: formContent(asset),
        confirmLabel: 'Simpan',
        extraFooter,
        onReady: (modal) => {
            modal.querySelector('[data-category-retry]')?.addEventListener('click', () => loadCategories());
        },
        onExtra: isEdit ? async () => {
            const ok = await confirmDialog(
                `Hapus aset "${asset.name}"? Tindakan ini tidak dapat dibatalkan.`
            );
            if (!ok) return false;

            await api.delete('/assets/' + asset.id);
            toast('Aset berhasil dihapus.');
            await load();
            return true;
        } : null,
        onConfirm: async (modal) => {
            const payload = {
                assets_code: fieldValue(modal, 'assets_code'),
                name: fieldValue(modal, 'name'),
                category_id: fieldValue(modal, 'category_id'),
                purchase_price: fieldValue(modal, 'purchase_price') || null,
                purchase_year: fieldValue(modal, 'purchase_year') || null,
            };

            // Jangan kirim status jika aset sedang dipinjam (mencegah AST-10).
            if (!borrowed) {
                payload.status = fieldValue(modal, 'status');
            }

            if (isEdit) {
                await api.put('/assets/' + asset.id, payload);
                toast('Aset disimpan.');
            } else {
                await api.post('/assets', payload);
                toast('Aset disimpan.');
            }
            await load();
            return true;
        },
    });
}

async function openDetail(id) {
    const errorBox = document.getElementById('assets-error');
    try {
        const data = await api.get('/assets/' + id + '/detail');
        const asset = data.asset;
        const loans = asset.loans || [];

        const holder = cloneTemplate('asset-detail-template');
        holder.querySelector('[data-detail="code"]').textContent = asset.assets_code ?? '-';
        holder.querySelector('[data-detail="name"]').textContent = asset.name ?? '-';
        holder.querySelector('[data-detail="category"]').textContent = asset.category?.name ?? '-';
        holder.querySelector('[data-detail="status"]').innerHTML = statusBadge(asset.status);
        holder.querySelector('[data-detail="price"]').textContent = formatPrice(asset.purchase_price);
        holder.querySelector('[data-detail="year"]').textContent = asset.purchase_year ?? '-';

        const table = holder.querySelector('[data-detail="history-table"]');
        const empty = holder.querySelector('[data-detail="history-empty"]');
        const body = holder.querySelector('[data-detail="history-body"]');

        table.hidden = loans.length === 0;
        empty.hidden = loans.length > 0;

        loans.forEach((loan) => {
            const row = cloneTemplate('asset-history-row-template').firstElementChild;
            row.querySelector('[data-slot="borrower"]').textContent = loan.borrower?.name || '-';
            row.querySelector('[data-slot="division"]').textContent = loan.borrower?.division?.name || '-';
            row.querySelector('[data-slot="borrowed"]').textContent = formatLoanDate(loan.borrowed_at);
            row.querySelector('[data-slot="returned"]').textContent = formatLoanDate(loan.returned_at);
            row.querySelector('[data-slot="status"]').innerHTML = statusBadge(loan.status);
            body.appendChild(row);
        });

        openModal({
            title: 'Detail Aset',
            contentHtml: holder.innerHTML,
            confirmLabel: 'Tutup',
            onConfirm: () => true,
        });
    } catch (error) {
        if (isPageLevelError(error)) {
            showPageError(document.getElementById('page-root'), error, () => window.location.reload());
            return;
        }
        errorState(errorBox, friendlyError(error), () => openDetail(id));
    }
}
