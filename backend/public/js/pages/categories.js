import { api } from '../api.js';
import {
    escapeHtml, emptyRow, toast, friendlyError,
    openModal, confirmDialog, fieldValue,
    errorState, clearErrorState, showPageError, isPageLevelError,
    registerSilentRefresh,
} from '../ui.js';

let categories = [];

/** Tabel dan summary sudah dirender Blade dari data server. */
export function initCategories() {
    const root = document.getElementById('page-root');
    try {
        categories = root.dataset.categories ? JSON.parse(root.dataset.categories) : [];
    } catch (e) {
        categories = [];
    }

    document.getElementById('btn-create')?.addEventListener('click', () => openForm());
    document.getElementById('category-search')?.addEventListener('input', render);

    document.getElementById('categories-body')?.addEventListener('click', async (event) => {
        const button = event.target.closest('button[data-action]');
        if (!button) return;

        const id = button.dataset.id;
        const category = categories.find((item) => String(item.id) === String(id));
        if (!category) return;

        if (button.dataset.action === 'edit') {
            openForm(category);
            return;
        }

        if (button.dataset.action === 'delete') {
            const ok = await confirmDialog(`Hapus kategori "${category.name}"?`);
            if (!ok) return;
            await deleteCategory(category);
        }
    });

    registerSilentRefresh(load);
}

async function deleteCategory(category) {
    const warnBox = document.getElementById('category-error');
    const actionBox = document.getElementById('category-action-error');

    try {
        await api.delete('/categories/' + category.id);
        warnBox.hidden = true;
        warnBox.textContent = '';
        clearErrorState(actionBox);
        toast('Kategori berhasil dihapus.');
        await load();
    } catch (error) {
        clearErrorState(actionBox);

        if (error.code === 'CATEGORY_HAS_ASSETS') {
            warnBox.textContent = 'Hapus kategori ditolak. ' + (error.message || '');
            warnBox.hidden = false;
            return;
        }

        warnBox.hidden = true;
        warnBox.textContent = '';
        if (isPageLevelError(error)) {
            showPageError(document.getElementById('page-root'), error, () => window.location.reload());
            return;
        }
        errorState(actionBox, friendlyError(error), () => deleteCategory(category));
    }
}

async function load() {
    const errorBox = document.getElementById('categories-error');
    const root = document.getElementById('page-root');

    try {
        const data = await api.get('/categories/refresh');
        categories = data.categories || [];
        clearErrorState(errorBox);
        render();
    } catch (error) {
        if (isPageLevelError(error)) {
            showPageError(root, error, () => window.location.reload());
            return;
        }

        document.getElementById('categories-body').innerHTML = '';
        document.getElementById('category-summary').textContent = '';
        errorState(errorBox, friendlyError(error), load);
    }
}

function render() {
    const keyword = (document.getElementById('category-search')?.value || '').trim().toLowerCase();
    const visible = categories.filter((category) =>
        String(category.name || '').toLowerCase().includes(keyword)
    );

    document.getElementById('categories-body').innerHTML = visible.length ? visible.map((category) => `
        <tr>
            <td class="cell-muted">${escapeHtml(category.id)}</td>
            <td><strong>${escapeHtml(category.name)}</strong></td>
            <td>${escapeHtml(category.assets_count ?? 0)}</td>
            <td>${escapeHtml(category.available_count ?? 0)}</td>
            <td>
                <div class="btn-row">
                    <button type="button" class="btn sec btn-sm" data-action="edit" data-id="${category.id}">Edit</button>
                    <button type="button" class="btn sec btn-sm" data-action="delete" data-id="${category.id}">Hapus</button>
                </div>
            </td>
        </tr>`).join('') : emptyRow(5, 'Belum ada data.');

    document.getElementById('category-summary').textContent =
        `Menampilkan ${visible.length} dari ${categories.length} kategori`;
}

function openForm(category = null) {
    const isEdit = !!category;
    const template = document.getElementById('category-form-template')?.innerHTML.trim() || '';
    const holder = document.createElement('div');
    holder.innerHTML = template;

    if (isEdit) holder.querySelector('[name="name"]').value = category.name || '';

    openModal({
        title: isEdit ? 'Edit Kategori' : 'Tambah Kategori',
        contentHtml: holder.innerHTML,
        confirmLabel: 'Simpan',
        onConfirm: async (modal) => {
            const payload = { name: fieldValue(modal, 'name') };
            if (isEdit) {
                await api.put('/categories/' + category.id, payload);
            } else {
                await api.post('/categories', payload);
            }
            toast('Kategori disimpan.');
            await load();
            return true;
        },
    });
}
