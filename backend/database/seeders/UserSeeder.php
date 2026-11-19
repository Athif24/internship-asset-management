<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('Password123!');

        // Nilai lama tidak diubah; updateOrInsert membuat seeder aman dijalankan ulang.
        $users = [
            ['id' => 1, 'name' => 'Admin Aset',     'email' => 'admin@example.com',  'password_hash' => $password, 'role' => 'ADMIN', 'division_id' => 1],
            ['id' => 2, 'name' => 'Budi Santoso',   'email' => 'budi@example.com',   'password_hash' => $password, 'role' => 'USER',  'division_id' => 1],
            ['id' => 3, 'name' => 'Sari Wulandari', 'email' => 'sari@example.com',   'password_hash' => $password, 'role' => 'USER',  'division_id' => 2],
            ['id' => 4, 'name' => 'Andi Pratama',   'email' => 'andi@example.com',   'password_hash' => $password, 'role' => 'USER',  'division_id' => 4],
            ['id' => 5, 'name' => 'Dewi Lestari',   'email' => 'dewi@example.com',   'password_hash' => $password, 'role' => 'USER',  'division_id' => 3],
            ['id' => 6, 'name' => 'Rizky Maulana',  'email' => 'rizky@example.com',  'password_hash' => $password, 'role' => 'USER',  'division_id' => 4],
        ];

        foreach ($users as $user) {
            DB::table('users')->updateOrInsert(['id' => $user['id']], $user);
        }
    }
}
