<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AssetSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('assets')->insert([
            ['id' => 1,  'assets_code' => 'AST-LPT-001', 'name' => 'Laptop Dell Latitude 5420',     'category_id' => 1, 'status' => 'AVAILABLE'],
            ['id' => 2,  'assets_code' => 'AST-LPT-002', 'name' => 'Laptop Lenovo ThinkPad T14',    'category_id' => 1, 'status' => 'BORROWED'],
            ['id' => 3,  'assets_code' => 'AST-PRJ-001', 'name' => 'Proyektor Epson EB-X06',        'category_id' => 2, 'status' => 'AVAILABLE'],
            ['id' => 4,  'assets_code' => 'AST-CAM-001', 'name' => 'Kamera Canon EOS 800D',         'category_id' => 3, 'status' => 'AVAILABLE'],
            ['id' => 5,  'assets_code' => 'AST-CAM-002', 'name' => 'Kamera Sony A6400',             'category_id' => 3, 'status' => 'BORROWED'],
            ['id' => 6,  'assets_code' => 'AST-UKR-001', 'name' => 'Total Station Topcon',          'category_id' => 4, 'status' => 'BORROWED'],
            ['id' => 7,  'assets_code' => 'AST-UKR-002', 'name' => 'Theodolite Digital Nikon',      'category_id' => 4, 'status' => 'MAINTENANCE'],
            ['id' => 8,  'assets_code' => 'AST-VHC-001', 'name' => 'Motor Operasional Honda Vario', 'category_id' => 5, 'status' => 'BORROWED'],
            ['id' => 9,  'assets_code' => 'AST-LPT-003', 'name' => 'Laptop Asus Vivobook 14',       'category_id' => 1, 'status' => 'AVAILABLE'],
            ['id' => 10, 'assets_code' => 'AST-PRJ-002', 'name' => 'Proyektor BenQ MX550',          'category_id' => 2, 'status' => 'AVAILABLE'],
        ]);
    }
}
