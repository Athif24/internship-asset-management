import { api, query } from '../api.js';
import {
    statusBadge, escapeHtml, emptyRow,
    friendlyError, options, errorState, clearErrorState,
    selectError, clearSelectError, showPageError, isPageLevelError,
    registerSilentRefresh,
} from '../ui.js';

/** Data awal sudah dirender Blade; JS hanya memperbarui bagian tertentu. */
export function initDashboard() {
    let timer;

    document.getElementById('filter-q')?.addEventListener('input', () => {
        window.clearTimeout(timer);
        timer = window.setTimeout(load, 250);
    });
    ['filter-status', 'filter-category', 'filter-division'].forEach((id) => {
        document.getElementById(id)?.addEventListener('change', load);
    });

    registerSilentRefresh(load);
}

async function loadDivisions() {
    const select = document.getElementById('filter-division');
    try {
        const data = await api.get('/divisions/refresh');
        clearSelectError(select);
        select.innerHTML = options(data.divisions || [], null, 'Semua divisi peminjam');
    } catch (error) {
        selectError(select, loadDivisions);
    }
}

async function loadCategories() {
    const select = document.getElementById('filter-category');
    try {
        const data = await api.get('/categories/refresh');
        clearSelectError(select);
        select.innerHTML = options(data.categories || [], null, 'Semua kategori');
    } catch (error) {
        selectError(select, loadCategories);
    }
}

function renderCards(summary = {}, failed = false) {
    const value = (key) => (failed ? '-' : (summary[key] ?? '-'));
    const cards = [
        { label: 'Total Aset', value: value('TOTAL'), style: '' },
        { label: 'Tersedia', value: value('AVAILABLE'), style: 'success' },
        { label: 'Dipinjam', value: value('BORROWED'), style: 'warning' },
        { label: 'Maintenance', value: value('MAINTENANCE'), style: 'secondary' },
    ];

    document.getElementById('stats').innerHTML = cards.map((card) => `
        <div class="stat-card ${card.style}">
            <div class="stat-label">${escapeHtml(card.label)}</div>
            <div class="stat-value">${escapeHtml(card.value)}</div>
        </div>`).join('');
}

function renderAssets(assets) {
    document.getElementById('assets-body').innerHTML = assets.length ? assets.map((asset) => {
        const borrowed = asset.status === 'BORROWED';
        const borrower = borrowed ? (asset.active_loan?.borrower?.name || '-') : '-';
        const division = borrowed ? (asset.active_loan?.borrower?.division?.name || '-') : '-';

        return `
        <tr>
            <td class="cell-code">${escapeHtml(asset.assets_code)}</td>
            <td>${escapeHtml(asset.name)}</td>
            <td>${escapeHtml(asset.category?.name || '-')}</td>
            <td>${statusBadge(asset.status)}</td>
            <td>${escapeHtml(borrower)}</td>
            <td>${escapeHtml(division)}</td>
        </tr>`;
    }).join('') : emptyRow(6, 'Belum ada data.');
}

async function load() {
    const params = query({
        q: document.getElementById('filter-q')?.value,
        status: document.getElementById('filter-status')?.value,
        category_id: document.getElementById('filter-category')?.value,
        division_id: document.getElementById('filter-division')?.value,
    });
    const errorBox = document.getElementById('dashboard-error');
    const root = document.getElementById('page-root');

    try {
        const data = await api.get('/dashboard/refresh' + params);

        clearErrorState(errorBox);
        // Kartu memakai ringkasan global dari server dan tidak ikut filter tabel.
        renderCards(data.summary || {});
        renderAssets(data.assets || []);
    } catch (error) {
        if (isPageLevelError(error)) {
            showPageError(root, error, () => window.location.reload());
            return;
        }

        renderCards({}, true);
        document.getElementById('assets-body').innerHTML = '';
        errorState(errorBox, friendlyError(error), load);
    }
}
