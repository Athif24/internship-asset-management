import { api } from '../api.js';
import {
    statusBadge, formatLoanDate, escapeHtml, emptyRow,
    friendlyError, options, errorState, clearErrorState,
    selectError, clearSelectError, showPageError, isPageLevelError,
    registerSilentRefresh,
} from '../ui.js';

const PAGE_SIZE = 5;

let allLoans = [];
let totalFromApi = null;
let currentPage = 1;

/** Tabel, kartu, dan filter periode sudah dirender Blade. */
export function initLoans() {
    const root = document.getElementById('page-root');
    try {
        allLoans = root.dataset.loans ? JSON.parse(root.dataset.loans) : [];
        totalFromApi = root.dataset.total !== undefined ? Number(root.dataset.total) : allLoans.length;
    } catch (e) {
        allLoans = [];
        totalFromApi = 0;
    }

    document.getElementById('filter-q')?.addEventListener('input', () => {
        currentPage = 1;
        render();
    });
    ['filter-status', 'filter-division', 'filter-period'].forEach((id) => {
        document.getElementById(id)?.addEventListener('change', () => {
            currentPage = 1;
            render();
        });
    });
    document.getElementById('btn-prev')?.addEventListener('click', () => {
        if (currentPage > 1) {
            currentPage -= 1;
            render();
        }
    });
    document.getElementById('btn-next')?.addEventListener('click', () => {
        currentPage += 1;
        render();
    });

    registerSilentRefresh(loadHistory);
    render();
}

async function loadDivisions() {
    const select = document.getElementById('filter-division');
    try {
        const data = await api.get('/divisions/refresh');
        clearSelectError(select);
        select.innerHTML = options(data.divisions || [], null, 'Semua divisi');
    } catch (error) {
        selectError(select, loadDivisions);
    }
}

function buildPeriodOptions(loans) {
    const months = [...new Set(
        loans
            .map((loan) => String(loan.borrowed_at || '').slice(0, 7))
            .filter((value) => /^\d{4}-\d{2}$/.test(value))
    )].sort().reverse();

    const items = months.map((value) => {
        const [year, month] = value.split('-').map(Number);
        const label = new Intl.DateTimeFormat('id-ID', {
            month: 'long',
            year: 'numeric',
        }).format(new Date(Date.UTC(year, month - 1, 1)));

        return `<option value="${value}">${escapeHtml(label)}</option>`;
    }).join('');

    return `<option value="">Semua periode</option>${items}`;
}

function renderStats(failed = false) {
    const total = failed ? '-' : (totalFromApi ?? allLoans.length);
    const cards = [
        { label: 'Total Transaksi', value: total, style: '' },
        {
            label: 'Sedang Dipinjam',
            value: failed ? '-' : allLoans.filter((loan) => loan.status === 'BORROWED').length,
            style: 'warning',
        },
        {
            label: 'Dikembalikan',
            value: failed ? '-' : allLoans.filter((loan) => loan.status === 'RETURNED').length,
            style: 'success',
        },
    ];

    document.getElementById('stats').innerHTML = cards.map((card) => `
        <div class="stat-card compact ${card.style}">
            <div class="stat-label">${escapeHtml(card.label)}</div>
            <div class="stat-value">${escapeHtml(card.value)}</div>
        </div>`).join('');
}

function filteredLoans() {
    const keyword = (document.getElementById('filter-q')?.value || '').trim().toLowerCase();
    const status = document.getElementById('filter-status')?.value || '';
    const divisionId = document.getElementById('filter-division')?.value || '';
    const period = document.getElementById('filter-period')?.value || '';

    return allLoans.filter((loan) => {
        const asset = loan.asset || {};
        const borrower = loan.borrower || {};
        const haystack = [asset.name, asset.assets_code, borrower.name]
            .filter(Boolean).join(' ').toLowerCase();

        if (keyword && !haystack.includes(keyword)) return false;
        if (status && loan.status !== status) return false;
        if (divisionId && String(borrower.division?.id) !== String(divisionId)) return false;
        // Periode memakai borrowed_at, bukan returned_at.
        if (period && !String(loan.borrowed_at || '').startsWith(period)) return false;
        return true;
    });
}

function hasActiveFilters() {
    return !!(
        document.getElementById('filter-q')?.value ||
        document.getElementById('filter-status')?.value ||
        document.getElementById('filter-division')?.value ||
        document.getElementById('filter-period')?.value
    );
}

function render() {
    const loans = filteredLoans();
    const totalPages = Math.max(1, Math.ceil(loans.length / PAGE_SIZE));

    if (currentPage > totalPages) currentPage = totalPages;

    const start = (currentPage - 1) * PAGE_SIZE;
    const rows = loans.slice(start, start + PAGE_SIZE);
    const shownFrom = loans.length ? start + 1 : 0;
    const shownTo = Math.min(start + PAGE_SIZE, loans.length);

    // Tanpa filter, total diambil dari server. Saat ada filter client-side, pakai jumlah hasil filter.
    const displayTotal = hasActiveFilters()
        ? loans.length
        : (totalFromApi ?? loans.length);

    document.getElementById('loans-body').innerHTML = rows.length ? rows.map((loan) => `
        <tr>
            <td class="cell-muted">${escapeHtml(loan.id)}</td>
            <td>
                <div>${escapeHtml(loan.asset?.name || '-')}</div>
                <div class="cell-code">${escapeHtml(loan.asset?.assets_code || '-')}</div>
            </td>
            <td>${escapeHtml(loan.borrower?.name || '-')}</td>
            <td>${escapeHtml(loan.borrower?.division?.name || '-')}</td>
            <td class="cell-nowrap">${formatLoanDate(loan.borrowed_at)}</td>
            <td class="cell-nowrap">${formatLoanDate(loan.returned_at)}</td>
            <td>${statusBadge(loan.status)}</td>
        </tr>`).join('') : emptyRow(7, 'Belum ada data.');

    document.getElementById('pagination-info').textContent =
        `Menampilkan ${shownFrom}-${shownTo} dari ${displayTotal}`;

    document.getElementById('btn-prev').disabled = currentPage <= 1;
    document.getElementById('btn-next').disabled = currentPage >= totalPages;
}

async function loadHistory() {
    const errorBox = document.getElementById('loans-error');
    const root = document.getElementById('page-root');
    const periodSelect = document.getElementById('filter-period');
    const selectedPeriod = periodSelect?.value || '';

    try {
        const data = await api.get('/loans/refresh');
        allLoans = data.loans || [];
        totalFromApi = Number.isFinite(data.total) ? data.total : allLoans.length;

        clearErrorState(errorBox);
        // Refresh tidak mereset filter/page: periode dipilih ulang bila masih tersedia.
        periodSelect.innerHTML = buildPeriodOptions(allLoans);
        if ([...periodSelect.options].some((option) => option.value === selectedPeriod)) {
            periodSelect.value = selectedPeriod;
        }
        renderStats();
        render();
    } catch (error) {
        if (isPageLevelError(error)) {
            showPageError(root, error, () => window.location.reload());
            return;
        }

        renderStats(true);
        document.getElementById('loans-body').innerHTML = '';
        document.getElementById('pagination-info').textContent = '';
        errorState(errorBox, friendlyError(error), loadHistory);
    }
}
