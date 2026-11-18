<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Api\AssetCategoryController as ApiCategoryController;
use App\Http\Controllers\Api\DashboardController as ApiDashboardController;
use App\Http\Controllers\Api\DivisionController as ApiDivisionController;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $data = app(ApiDashboardController::class)->index($request)->getData(true)['data'];

        return view('dashboard', [
            'page' => 'dashboard',
            'title' => 'Dashboard',
            'summary' => $data['summary'],
            'assets' => $data['assets'],
            'categories' => app(ApiCategoryController::class)->index()->getData(true)['data']['categories'],
            'divisions' => app(ApiDivisionController::class)->index()->getData(true)['data']['divisions'],
        ]);
    }

    public function refresh(Request $request): JsonResponse
    {
        return app(ApiDashboardController::class)->index($request);
    }
}
