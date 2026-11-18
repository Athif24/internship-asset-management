<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DivisionSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('divisions')->insert([
            ['id' => 1, 'name' => 'IT'],
            ['id' => 2, 'name' => 'HR'],
            ['id' => 3, 'name' => 'Finance'],
            ['id' => 4, 'name' => 'Operations'],
            ['id' => 5, 'name' => 'Marketing'],
        ]);
    }
}
