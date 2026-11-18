@php
    $loans = $loans ?? collect();
    $divisions = $divisions ?? collect();
    $total = $total ?? $loans->count();

    $periods = $loans
        ->map(fn ($loan) => $loan->borrowed_at?->format('Y-m'))
        ->filter()
        ->unique()
        ->sortDesc()
        ->values();
    $periodMonths = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

    $loanCards = [
        ['label' => 'Total Transaksi', 'value' => $total, 'style' => ''],
        ['label' => 'Sedang Dipinjam', 'value' => $loans->where('status', 'BORROWED')->count(), 'style' => 'warning'],
        ['label' => 'Dikembalikan', 'value' => $loans->where('status', 'RETURNED')->count(), 'style' => 'success'],
    ];
@endphp

<div class="page-head">
    <h1>Riwayat Peminjaman</h1>
    <span class="who">Semua transaksi karyawan</span>
</div>

<div class="stat-grid" id="stats">
    @foreach ($loanCards as $card)
        <div class="stat-card compact {{ $card['style'] }}">
            <div class="stat-label">{{ $card['label'] }}</div>
            <div class="stat-value">{{ $card['value'] }}</div>
        </div>
    @endforeach
</div>

<div class="card">
    <div class="filter-bar">
        <div class="filter-group">
            <label for="filter-q">Cari</label>
            <input type="text" id="filter-q" placeholder="Cari aset atau peminjam">
        </div>
        <div class="filter-group">
            <label for="filter-status">Status</label>
            <select id="filter-status">
                <option value="">Semua status</option>
                <option value="BORROWED">BORROWED</option>
                <option value="RETURNED">RETURNED</option>
            </select>
        </div>
        <div class="filter-group">
            <label for="filter-division">Divisi</label>
            <select id="filter-division">
                <option value="">Semua divisi</option>
                @foreach ($divisions as $division)
                    <option value="{{ $division->id }}">{{ $division->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="filter-group">
            <label for="filter-period">Periode</label>
            <select id="filter-period">
                <option value="">Semua periode</option>
                @foreach ($periods as $period)
                    @php
                        [$periodYear, $periodMonth] = explode('-', $period);
                    @endphp
                    <option value="{{ $period }}">{{ $periodMonths[(int) $periodMonth - 1] }} {{ $periodYear }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="page-error" id="loans-error" hidden></div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>ASET</th>
                    <th>PEMINJAM</th>
                    <th>DIVISI</th>
                    <th>DIPINJAM</th>
                    <th>DIKEMBALIKAN</th>
                    <th>STATUS</th>
                </tr>
            </thead>
            <tbody id="loans-body">
                @forelse ($loans->take(5) as $loan)
                    <tr>
                        <td class="cell-muted">{{ $loan->id }}</td>
                        <td>
                            <div>{{ $loan->asset?->name ?? '-' }}</div>
                            <div class="cell-code">{{ $loan->asset?->assets_code ?? '-' }}</div>
                        </td>
                        <td>{{ $loan->borrower?->name ?? '-' }}</td>
                        <td>{{ $loan->borrower?->division?->name ?? '-' }}</td>
                        <td class="cell-nowrap">@include('partials.loan-date', ['date' => $loan->borrowed_at])</td>
                        <td class="cell-nowrap">@include('partials.loan-date', ['date' => $loan->returned_at])</td>
                        <td>@include('partials.status-badge', ['status' => $loan->status])</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="empty-state"><strong>Tidak ada data</strong>Belum ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-row">
        <span class="pagination-info" id="pagination-info">Menampilkan 1-{{ min(5, $loans->count()) }} dari {{ $total }}</span>
        <div class="pagination-actions">
            <button type="button" class="btn sec btn-sm" id="btn-prev" disabled>Sebelumnya</button>
            <button type="button" class="btn sec btn-sm" id="btn-next" @if ($loans->count() <= 5) disabled @endif>Berikutnya</button>
        </div>
    </div>
</div>
