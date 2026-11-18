<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAssetLoanRequest;
use App\Models\AssetLoan;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssetLoanController extends Controller
{
    /**
     * AST-02 / AST-03 / AST-04 / AST-08 / AST-09.
     * Prosedur yang mengelola transaksi, lock, dan rollback.
     */
    public function borrow(StoreAssetLoanRequest $request): JsonResponse
    {
        DB::statement('CALL borrow_asset(?, ?, @loan_id)', [
            $request->integer('asset_id'),
            auth()->id(),
        ]);

        $loanId = DB::select('SELECT @loan_id AS loan_id')[0]->loan_id;
        $loan = AssetLoan::with(['asset.category', 'borrower.division'])->findOrFail($loanId);

        return ApiResponse::success(['loan' => $loan], 'Aset berhasil dipinjam.', 201);
    }

    /**
     * AST-05 / AST-06 / AST-07.
     * Route {id} adalah id pinjaman; prosedur menerima id aset.
     */
    public function returnLoan(int $id): JsonResponse
    {
        $loan = AssetLoan::findOrFail($id);

        DB::statement('CALL return_asset(?, ?, @loan_id)', [
            $loan->assets_id,
            auth()->id(),
        ]);

        $returnedLoan = AssetLoan::with(['asset.category', 'borrower.division'])->findOrFail($id);

        return ApiResponse::success(['loan' => $returnedLoan], 'Aset berhasil dikembalikan.');
    }

    public function history(Request $request): JsonResponse
    {
        $query = AssetLoan::with(['asset.category', 'borrower.division']);

        if (auth()->user()->role !== 'ADMIN') {
            $query->where('borrower_id', auth()->id());
        }

        if ($request->filled('status')) {
            $query->where('status', (string) $request->string('status'));
        }

        if ($request->filled('asset_id')) {
            $query->where('assets_id', $request->integer('asset_id'));
        }

        if ($request->filled('division_id')) {
            $query->whereHas('borrower', function ($sub) use ($request) {
                $sub->where('division_id', $request->integer('division_id'));
            });
        }

        $loans = $query->orderByDesc('borrowed_at')->get();

        return ApiResponse::success([
            'loans' => $loans,
            'total' => $loans->count(),
        ], 'Riwayat transaksi berhasil diambil.');
    }
}
