<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAssetCategoryRequest;
use App\Http\Requests\UpdateAssetCategoryRequest;
use App\Models\AssetCategory;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class AssetCategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = AssetCategory::withCount([
            'assets',
            'assets as available_count' => function ($query) {
                $query->where('status', 'AVAILABLE');
            },
        ])->orderBy('name')->get();

        return ApiResponse::success(
            ['categories' => $categories],
            'Daftar kategori berhasil diambil.'
        );
    }

    public function store(StoreAssetCategoryRequest $request): JsonResponse
    {
        $category = AssetCategory::create($request->validated());

        return ApiResponse::success(['category' => $category], 'Kategori berhasil dibuat.', 201);
    }

    public function update(UpdateAssetCategoryRequest $request, AssetCategory $category): JsonResponse
    {
        $category->update($request->validated());

        return ApiResponse::success(['category' => $category], 'Kategori berhasil diperbarui.');
    }

    public function destroy(AssetCategory $category): JsonResponse
    {
        $total = $category->assets()->count();

        if ($total > 0) {
            return ApiResponse::error(
                "Kategori {$category->name} masih memiliki {$total} aset dan tidak dapat dihapus (HTTP 409).",
                'CATEGORY_HAS_ASSETS',
                ['name' => $category->name, 'assets' => $total],
                409
            );
        }

        $category->delete();

        return ApiResponse::success(null, 'Kategori berhasil dihapus.');
    }
}
