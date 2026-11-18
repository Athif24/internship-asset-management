<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetLoan;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $counts = Asset::query()
            ->selectRaw('status, COUNT(*) AS total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $summary = [
            'TOTAL' => Asset::count(),
            'AVAILABLE' => (int) $counts->get('AVAILABLE', 0),
            'BORROWED' => (int) $counts->get('BORROWED', 0),
            'MAINTENANCE' => (int) $counts->get('MAINTENANCE', 0),
            'ACTIVE_LOANS' => AssetLoan::active()->count(),
        ];

        $loans = AssetLoan::with(['asset.category', 'borrower.division']);

        if ($request->filled('status')) {
            $loans->where('status', (string) $request->string('status'));
        }

        if ($request->filled('category_id')) {
            $loans->whereHas('asset', function ($sub) use ($request) {
                $sub->where('category_id', $request->integer('category_id'));
            });
        }

        if ($request->filled('division_id')) {
            $loans->whereHas('borrower', function ($sub) use ($request) {
                $sub->where('division_id', $request->integer('division_id'));
            });
        }

        $recentLoans = $loans->orderByDesc('borrowed_at')->limit(10)->get();

        // Dashboard Web menampilkan ASET, bukan transaksi. Ringkasan dan
        // recent loans tetap dipertahankan agar response lama tidak rusak.
        $assetsQuery = Asset::with(['category', 'activeLoan.borrower.division']);

        if ($request->filled('status')) {
            $assetsQuery->where('status', (string) $request->string('status'));
        }

        if ($request->filled('category_id')) {
            $assetsQuery->where('category_id', $request->integer('category_id'));
        }

        if ($request->filled('division_id')) {
            $assetsQuery->whereHas('activeLoan.borrower', function ($sub) use ($request) {
                $sub->where('division_id', $request->integer('division_id'));
            });
        }

        if ($request->filled('q')) {
            $search = (string) $request->string('q');
            $assetsQuery->where(function ($sub) use ($search) {
                $sub->where('name', 'like', "%{$search}%")
                    ->orWhere('assets_code', 'like', "%{$search}%");
            });
        }

        $assets = $assetsQuery->orderBy('id')->get();

        return ApiResponse::success([
            'summary' => $summary,
            'loans' => $recentLoans,
            'assets' => $assets,
        ], 'Data dashboard berhasil diambil.');
    }
}
