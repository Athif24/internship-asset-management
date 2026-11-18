<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAssetRequest;
use App\Http\Requests\UpdateAssetRequest;
use App\Models\Asset;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssetController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Asset::with('category');

        if ($request->filled('status')) {
            $query->where('status', (string) $request->string('status'));
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }

        if ($request->filled('q')) {
            $search = (string) $request->string('q');
            $query->where(function ($sub) use ($search) {
                $sub->where('name', 'like', "%{$search}%")
                    ->orWhere('assets_code', 'like', "%{$search}%");
            });
        }

        $assets = $query->orderBy('id')->get();

        return ApiResponse::success([
            'assets' => $assets,
            'total' => $assets->count(),
        ], 'Daftar aset berhasil diambil.');
    }

    public function show(Asset $asset): JsonResponse
    {
        $asset->load(['category', 'loans.borrower.division']);

        return ApiResponse::success(['asset' => $asset], 'Detail aset berhasil diambil.');
    }

    public function store(StoreAssetRequest $request): JsonResponse
    {
        $asset = Asset::create($request->validated());

        return ApiResponse::success(['asset' => $asset->load('category')], 'Aset berhasil dibuat.', 201);
    }

    public function update(UpdateAssetRequest $request, Asset $asset): JsonResponse
    {
        $data = $request->validated();

        // Menjaga konsistensi: aset dengan pinjaman aktif tidak boleh diubah statusnya manual.
        if (isset($data['status']) && $data['status'] !== $asset->status && $asset->activeLoan()->exists()) {
            return ApiResponse::error(
                'Status tidak dapat diubah saat aset sedang dipinjam.',
                'AST-10',
                null,
                409
            );
        }

        $asset->update($data);

        return ApiResponse::success(['asset' => $asset->load('category')], 'Aset berhasil diperbarui.');
    }

    /**
     * Hapus aset hanya jika tidak ada pinjaman aktif maupun riwayat.
     * Riwayat dipertahankan untuk integritas UAT dan query konsistensi.
     */
    public function destroy(Asset $asset): JsonResponse
    {
        if ($asset->activeLoan()->exists()) {
            return ApiResponse::error(
                'Aset sedang dipinjam.',
                'ASSET_STILL_BORROWED',
                null,
                409
            );
        }

        if ($asset->loans()->exists()) {
            return ApiResponse::error(
                'Aset memiliki riwayat pinjaman dan tidak dapat dihapus.',
                'ASSET_HAS_HISTORY',
                null,
                409
            );
        }

        $asset->delete();

        return ApiResponse::success(null, 'Aset berhasil dihapus.');
    }
}
