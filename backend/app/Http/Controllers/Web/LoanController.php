<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Api\AssetLoanController as ApiLoanController;
use App\Http\Controllers\Api\DivisionController as ApiDivisionController;
use App\Http\Controllers\Controller;
use App\Models\AssetLoan;
use App\Models\Division;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LoanController extends Controller
{
    public function index(): View
    {
        $loans = AssetLoan::with('asset', 'borrower.division')
            ->orderByDesc('borrowed_at')
            ->get();

        return view('loans', [
            'page' => 'loans',
            'title' => 'Riwayat Pinjam',
            'loans' => $loans,
            'total' => $loans->count(),
            'divisions' => Division::orderBy('id')->get(),
        ]);
    }

    public function refresh(Request $request): JsonResponse
    {
        return app(ApiLoanController::class)->history($request);
    }

    public function divisions(): JsonResponse
    {
        return app(ApiDivisionController::class)->index();
    }
}
