<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AssetCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['id' => 1, 'name' => 'Laptop'],
            ['id' => 2, 'name' => 'Proyektor'],
            ['id' => 3, 'name' => 'Kamera'],
            ['id' => 4, 'name' => 'Alat Ukur'],
            ['id' => 5, 'name' => 'Kendaraan Operasional'],
        ];

        foreach ($categories as $category) {
            DB::table('assets_categories')->updateOrInsert(['id' => $category['id']], $category);
        }
    }
}
