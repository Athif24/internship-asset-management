@php
    $assets = $assets ?? collect();
    $categories = $categories ?? collect();
@endphp

<div class="page-head">
    <h1>Kelola Aset</h1>
    <button type="button" class="btn btn-primary" id="btn-create">Tambah Aset</button>
</div>

<div class="card">
    <div class="page-error" id="asset-category-error" hidden></div>
    <div class="page-error" id="assets-error" hidden></div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>KODE</th>
                    <th>NAMA</th>
                    <th>KATEGORI</th>
                    <th>STATUS</th>
                    <th>AKSI</th>
                </tr>
            </thead>
            <tbody id="assets-body">
                @forelse ($assets as $asset)
                    <tr>
                        <td class="cell-code">{{ data_get($asset, 'assets_code') }}</td>
                        <td>
                            <button type="button" class="name-link" data-detail="{{ data_get($asset, 'id') }}">
                                {{ data_get($asset, 'name') }}
                            </button>
                        </td>
                        <td>{{ data_get($asset, 'category.name', '-') }}</td>
                        <td>@include('partials.status-badge', ['status' => data_get($asset, 'status')])</td>
                        <td>
                            <button type="button" class="btn sec btn-sm" data-action="edit" data-id="{{ data_get($asset, 'id') }}">Edit</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty-state"><strong>Tidak ada data</strong>Belum ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Template modal form dipindah dari JS ke Blade. --}}
<template id="asset-form-template">
    <div class="alert-error" id="form-error" hidden></div>
    <div class="form-group">
        <label class="form-label" for="f-code">Kode aset</label>
        <input class="form-control" type="text" name="assets_code" id="f-code" required>
    </div>
    <div class="form-group">
        <label class="form-label" for="f-name">Nama aset</label>
        <input class="form-control" type="text" name="name" id="f-name" required>
    </div>
    <div class="form-group" id="f-category-group">
        <label class="form-label" for="f-category">Kategori</label>
        <select class="form-control" name="category_id" id="f-category" required>
            <option value="">Pilih kategori</option>
            @foreach ($categories as $category)
                <option value="{{ data_get($category, 'id') }}">{{ data_get($category, 'name') }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group" id="f-status-group">
        <label class="form-label" for="f-status">Status</label>
        <select class="form-control" name="status" id="f-status">
            <option value="AVAILABLE">AVAILABLE</option>
            <option value="MAINTENANCE">MAINTENANCE</option>
        </select>
    </div>
</template>

<template id="asset-status-locked-template">
    <div class="form-group" id="f-status-group">
        <label class="form-label" for="f-status">Status</label>
        <div>@include('partials.status-badge', ['status' => 'BORROWED'])</div>
        <p class="form-hint">Status tidak dapat diubah saat aset sedang dipinjam.</p>
    </div>
</template>

<template id="asset-detail-template">
    <dl class="detail-list">
        <dt>Kode</dt><dd class="cell-code" data-detail="code"></dd>
        <dt>Nama</dt><dd data-detail="name"></dd>
        <dt>Kategori</dt><dd data-detail="category"></dd>
        <dt>Status</dt><dd data-detail="status"></dd>
    </dl>
    <div class="detail-section-title">Riwayat Pinjaman</div>
    <div class="table-wrap" data-detail="history-table">
        <table>
            <thead><tr><th>Peminjam</th><th>Divisi</th><th>Dipinjam</th><th>Dikembalikan</th><th>Status</th></tr></thead>
            <tbody data-detail="history-body"></tbody>
        </table>
    </div>
    <p class="cell-muted" data-detail="history-empty" hidden>Aset ini belum memiliki riwayat pinjaman.</p>
</template>

<template id="asset-history-row-template">
    <tr>
        <td data-slot="borrower"></td>
        <td data-slot="division"></td>
        <td class="cell-nowrap" data-slot="borrowed"></td>
        <td class="cell-nowrap" data-slot="returned"></td>
        <td data-slot="status"></td>
    </tr>
</template>
