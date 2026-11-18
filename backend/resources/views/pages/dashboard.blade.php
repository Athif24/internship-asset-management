@php
    $summary = $summary ?? [];
    $assets = $assets ?? collect();
    $categories = $categories ?? collect();
    $divisions = $divisions ?? collect();

    $summaryCards = [
        ['label' => 'Total Aset', 'value' => $summary['TOTAL'] ?? '-', 'style' => ''],
        ['label' => 'Tersedia', 'value' => $summary['AVAILABLE'] ?? '-', 'style' => 'success'],
        ['label' => 'Dipinjam', 'value' => $summary['BORROWED'] ?? '-', 'style' => 'warning'],
        ['label' => 'Maintenance', 'value' => $summary['MAINTENANCE'] ?? '-', 'style' => 'secondary'],
    ];
@endphp

<div class="page-head">
    <h1>Dashboard Aset</h1>
    <span class="who" id="header-user">Admin Aset · {{ auth()->user()?->email ?? '-' }}</span>
</div>

<div class="stat-grid" id="stats">
    @foreach ($summaryCards as $card)
        <div class="stat-card {{ $card['style'] }}">
            <div class="stat-label">{{ $card['label'] }}</div>
            <div class="stat-value">{{ $card['value'] }}</div>
        </div>
    @endforeach
</div>

<div class="card">
    <div class="filter-bar">
        <div class="filter-group">
            <label for="filter-q">Cari</label>
            <input type="text" id="filter-q" placeholder="Cari kode atau nama aset">
        </div>
        <div class="filter-group">
            <label for="filter-status">Status</label>
            <select id="filter-status">
                <option value="">Semua status</option>
                <option value="AVAILABLE">AVAILABLE</option>
                <option value="BORROWED">BORROWED</option>
                <option value="MAINTENANCE">MAINTENANCE</option>
            </select>
        </div>
        <div class="filter-group">
            <label for="filter-category">Kategori</label>
            <select id="filter-category">
                <option value="">Semua kategori</option>
                @foreach ($categories as $category)
                    <option value="{{ data_get($category, 'id') }}">{{ data_get($category, 'name') }}</option>
                @endforeach
            </select>
        </div>
        <div class="filter-group">
            <label for="filter-division">Divisi</label>
            <select id="filter-division">
                <option value="">Semua divisi peminjam</option>
                @foreach ($divisions as $division)
                    <option value="{{ data_get($division, 'id') }}">{{ data_get($division, 'name') }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="page-error" id="dashboard-error" hidden></div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>KODE</th>
                    <th>NAMA ASET</th>
                    <th>KATEGORI</th>
                    <th>STATUS</th>
                    <th>PEMINJAM</th>
                    <th>DIVISI</th>
                </tr>
            </thead>
            <tbody id="assets-body">
                @forelse ($assets as $asset)
                    <tr>
                        <td class="cell-code">{{ data_get($asset, 'assets_code') }}</td>
                        <td>{{ data_get($asset, 'name') }}</td>
                        <td>{{ data_get($asset, 'category.name', '-') }}</td>
                        <td>@include('partials.status-badge', ['status' => data_get($asset, 'status')])</td>
                        <td>{{ data_get($asset, 'status') === 'BORROWED' ? data_get($asset, 'active_loan.borrower.name', '-') : '-' }}</td>
                        <td>{{ data_get($asset, 'status') === 'BORROWED' ? data_get($asset, 'active_loan.borrower.division.name', '-') : '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="loading-state">Memuat data...</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
