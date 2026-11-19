<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AssetLoanSeeder extends Seeder
{
    public function run(): void
    {
        // 8 transaksi tetap. Hanya borrower_id baris 7 dan 8 yang diubah menjadi 3
        // agar 5 USER terbagi: 2 sedang meminjam, 2 sudah kembali, 1 belum pinjam.
        $loans = [
            ['id' => 1, 'assets_id' => 1,  'borrower_id' => 2, 'borrowed_at' => '2026-10-01 09:00:00', 'returned_at' => '2026-10-03 16:00:00', 'status' => 'RETURNED'],
            ['id' => 2, 'assets_id' => 3,  'borrower_id' => 3, 'borrowed_at' => '2026-10-05 08:30:00', 'returned_at' => '2026-10-05 17:00:00', 'status' => 'RETURNED'],
            ['id' => 3, 'assets_id' => 4,  'borrower_id' => 4, 'borrowed_at' => '2026-10-10 10:00:00', 'returned_at' => '2026-10-14 15:30:00', 'status' => 'RETURNED'],
            ['id' => 4, 'assets_id' => 1,  'borrower_id' => 5, 'borrowed_at' => '2026-10-20 09:00:00', 'returned_at' => '2026-10-22 11:00:00', 'status' => 'RETURNED'],
            ['id' => 5, 'assets_id' => 2,  'borrower_id' => 3, 'borrowed_at' => '2026-11-10 09:15:00', 'returned_at' => null, 'status' => 'BORROWED'],
            ['id' => 6, 'assets_id' => 5,  'borrower_id' => 2, 'borrowed_at' => '2026-11-12 13:00:00', 'returned_at' => null, 'status' => 'BORROWED'],
            ['id' => 7, 'assets_id' => 6,  'borrower_id' => 3, 'borrowed_at' => '2026-11-15 07:45:00', 'returned_at' => null, 'status' => 'BORROWED'],
            ['id' => 8, 'assets_id' => 8,  'borrower_id' => 3, 'borrowed_at' => '2026-11-16 08:00:00', 'returned_at' => null, 'status' => 'BORROWED'],
        ];

        foreach ($loans as $loan) {
            DB::table('assets_loan')->updateOrInsert(['id' => $loan['id']], $loan);
        }
    }
}
