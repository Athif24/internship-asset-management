<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Api\AssetCategoryController as ApiCategoryController;
use App\Http\Controllers\Api\AssetController as ApiAssetController;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAssetRequest;
use App\Http\Requests\UpdateAssetRequest;
use App\Models\Asset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssetController extends Controller
{
    public function index(Request $request): View
    {
        // Satu sumber data dengan API: rule AST-01 / AST-10 tidak ditulis ulang di sini.
        $assets = app(ApiAssetController::class)->index($request)->getData(true)['data']['assets'];
        $categories = app(ApiCategoryController::class)->index()->getData(true)['data']['categories'];

        return view('assets', [
            'page' => 'assets',
            'title' => 'Aset',
            'assets' => $assets,
            'categories' => $categories,
        ]);
    }

    public function refresh(Request $request): JsonResponse
    {
        return app(ApiAssetController::class)->index($request);
    }

    public function show(Asset $asset): JsonResponse
    {
        return app(ApiAssetController::class)->show($asset);
    }

    public function store(StoreAssetRequest $request): JsonResponse
    {
        return app(ApiAssetController::class)->store($request);
    }

    public function update(UpdateAssetRequest $request, Asset $asset): JsonResponse
    {
        return app(ApiAssetController::class)->update($request, $asset);
    }

    public function destroy(Asset $asset): JsonResponse
    {
        return app(ApiAssetController::class)->destroy($asset);
    }
}
