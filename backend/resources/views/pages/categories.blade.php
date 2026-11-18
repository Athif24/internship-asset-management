@php
    $categories = $categories ?? collect();
@endphp

<div class="page-head">
    <h1>Kategori Aset</h1>
    <button type="button" class="btn btn-primary" id="btn-create">Tambah Kategori</button>
</div>

<div class="alert-warn" id="category-error" hidden></div>

<div class="card">
    <input type="text" id="category-search" class="category-search" placeholder="Cari kategori">
    <div class="page-error" id="category-action-error" hidden></div>
    <div class="page-error" id="categories-error" hidden></div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th style="width:70px">ID</th>
                    <th>NAMA KATEGORI</th>
                    <th>JUMLAH ASET</th>
                    <th>TERSEDIA</th>
                    <th>AKSI</th>
                </tr>
            </thead>
            <tbody id="categories-body">
                @forelse ($categories as $category)
                    <tr>
                        <td class="cell-muted">{{ data_get($category, 'id') }}</td>
                        <td><strong>{{ data_get($category, 'name') }}</strong></td>
                        <td>{{ data_get($category, 'assets_count', 0) }}</td>
                        <td>{{ data_get($category, 'available_count', 0) }}</td>
                        <td>
                            <div class="btn-row">
                                <button type="button" class="btn sec btn-sm" data-action="edit" data-id="{{ data_get($category, 'id') }}">Edit</button>
                                <button type="button" class="btn sec btn-sm" data-action="delete" data-id="{{ data_get($category, 'id') }}">Hapus</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty-state"><strong>Tidak ada data</strong>Belum ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-info" id="category-summary">
        Menampilkan {{ count($categories) }} dari {{ count($categories) }} kategori
    </div>
</div>

<template id="category-form-template">
    <div class="alert-error" id="form-error" hidden></div>
    <div class="form-group">
        <label class="form-label" for="f-name">Nama kategori</label>
        <input class="form-control" type="text" name="name" id="f-name" required>
    </div>
</template>
