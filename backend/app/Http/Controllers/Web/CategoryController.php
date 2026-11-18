<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Api\AssetCategoryController as ApiCategoryController;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAssetCategoryRequest;
use App\Http\Requests\UpdateAssetCategoryRequest;
use App\Models\AssetCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        return view('categories', [
            'page' => 'categories',
            'title' => 'Kategori',
            'categories' => app(ApiCategoryController::class)->index()->getData(true)['data']['categories'],
        ]);
    }

    public function refresh(): JsonResponse
    {
        return app(ApiCategoryController::class)->index();
    }

    public function store(StoreAssetCategoryRequest $request): JsonResponse
    {
        return app(ApiCategoryController::class)->store($request);
    }

    public function update(UpdateAssetCategoryRequest $request, AssetCategory $category): JsonResponse
    {
        return app(ApiCategoryController::class)->update($request, $category);
    }

    public function destroy(AssetCategory $category): JsonResponse
    {
        return app(ApiCategoryController::class)->destroy($category);
    }
}
