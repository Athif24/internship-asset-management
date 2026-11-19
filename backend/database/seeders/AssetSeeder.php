<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AssetSeeder extends Seeder
{
    public function run(): void
    {
        // Kolom lama (kode, nama, kategori, status) tidak diubah pada aset yang
        // sudah ada: status mencerminkan transaksi pinjaman aktif, bukan data seed.
        // Dua kolom baru diisi untuk seluruh 10 aset.
        $assets = [
            ['id' => 1,  'assets_code' => 'AST-LPT-001', 'name' => 'Laptop Dell Latitude 5420',     'category_id' => 1, 'status' => 'AVAILABLE',   'purchase_price' => 12500000, 'purchase_year' => 2022],
            ['id' => 2,  'assets_code' => 'AST-LPT-002', 'name' => 'Laptop Lenovo ThinkPad T14',    'category_id' => 1, 'status' => 'BORROWED',    'purchase_price' => 18750000, 'purchase_year' => 2023],
            ['id' => 3,  'assets_code' => 'AST-PRJ-001', 'name' => 'Proyektor Epson EB-X06',        'category_id' => 2, 'status' => 'AVAILABLE',   'purchase_price' => 6200000,  'purchase_year' => 2021],
            ['id' => 4,  'assets_code' => 'AST-CAM-001', 'name' => 'Kamera Canon EOS 800D',         'category_id' => 3, 'status' => 'AVAILABLE',   'purchase_price' => 9800000,  'purchase_year' => 2020],
            ['id' => 5,  'assets_code' => 'AST-CAM-002', 'name' => 'Kamera Sony A6400',             'category_id' => 3, 'status' => 'BORROWED',    'purchase_price' => 14500000, 'purchase_year' => 2022],
            ['id' => 6,  'assets_code' => 'AST-UKR-001', 'name' => 'Total Station Topcon',          'category_id' => 4, 'status' => 'BORROWED',    'purchase_price' => 85000000, 'purchase_year' => 2019],
            ['id' => 7,  'assets_code' => 'AST-UKR-002', 'name' => 'Theodolite Digital Nikon',      'category_id' => 4, 'status' => 'MAINTENANCE', 'purchase_price' => 62000000, 'purchase_year' => 2020],
            ['id' => 8,  'assets_code' => 'AST-VHC-001', 'name' => 'Motor Operasional Honda Vario', 'category_id' => 5, 'status' => 'BORROWED',    'purchase_price' => 21000000, 'purchase_year' => 2021],
            ['id' => 9,  'assets_code' => 'AST-LPT-003', 'name' => 'Laptop Asus Vivobook 14',       'category_id' => 1, 'status' => 'AVAILABLE',   'purchase_price' => 8900000,  'purchase_year' => 2024],
            ['id' => 10, 'assets_code' => 'AST-PRJ-002', 'name' => 'Proyektor BenQ MX550',          'category_id' => 2, 'status' => 'AVAILABLE',   'purchase_price' => 5400000,  'purchase_year' => 2023],
        ];

        foreach ($assets as $asset) {
            $exists = DB::table('assets')->where('id', $asset['id'])->exists();

            if ($exists) {
                DB::table('assets')->where('id', $asset['id'])->update([
                    'purchase_price' => $asset['purchase_price'],
                    'purchase_year' => $asset['purchase_year'],
                ]);
            } else {
                DB::table('assets')->insert($asset);
            }
        }
    }
}
