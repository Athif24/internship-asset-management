<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DivisionSeeder extends Seeder
{
    public function run(): void
    {
        $divisions = [
            ['id' => 1, 'name' => 'IT'],
            ['id' => 2, 'name' => 'HR'],
            ['id' => 3, 'name' => 'Finance'],
            ['id' => 4, 'name' => 'Operations'],
            ['id' => 5, 'name' => 'Marketing'],
        ];

        foreach ($divisions as $division) {
            DB::table('divisions')->updateOrInsert(['id' => $division['id']], $division);
        }
    }
}
