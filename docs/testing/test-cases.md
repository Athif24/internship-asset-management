# Test Cases — Web Admin

Status: `Not Run` | `Pass` | `Fail` | `Blocked`

| ID | Requirement | Skenario | Precondition | Langkah | Expected | Actual | Status | Evidence |
|---|---|---|---|---|---|---|---|---|
| TC-001 | Login Web Admin | Akun USER mencoba login di laman admin | Tersedia akun `budi@example.com` dengan role `USER` dan password `Password123!` | 1. Buka `/login`<br>2. Isi email `budi@example.com` dan password `Password123!`<br>3. Klik tombol **Masuk** | Kembali ke form login; muncul pesan: "Email ini terdaftar sebagai USER, bukan ADMIN. Akun USER hanya dapat login melalui aplikasi mobile. Hubungi administrator jika Anda seharusnya memiliki akses admin."; session USER tidak aktif | Sesuai dengan expected | Pass | `php artisan test --filter=test_user_tidak_boleh_login_di_web_admin` → `1 passed (5 assertions)` |

Catatan: test di atas menggunakan guard `web` (session), bukan token Sanctum, karena yang diuji adalah laman admin.

| TC-002 | Kelola Aset | Tambah aset dengan harga & tahun beli | Login sebagai ADMIN, buka `/assets` | 1. Klik **Tambah Aset**<br>2. Isi kode, nama, kategori, harga `15000000`, tahun `2023`<br>3. Simpan | Aset tersimpan; tabel menampilkan `Rp15.000.000 · 2023` | Sesuai dengan expected | Pass | `php artisan test --filter=test_admin_dapat_menyimpan_harga_dan_tahun_beli` |
| TC-003 | Kelola Aset | Edit harga & tahun aset yang sudah ada | Aset `AST-LPT-001` sudah memiliki data | 1. Klik **Edit** pada aset tersebut<br>2. Ubah harga jadi `13000000`, tahun jadi `2024`<br>3. Simpan | Nilai baru tersimpan dan tampil di tabel/detail | Sesuai dengan expected | Pass | `php artisan test --filter=test_update_harga_dan_tahun_beli_berhasil` |
| TC-004 | Kelola Aset | Validasi input harga & tahun | Login sebagai ADMIN | 1. Tambah aset dengan harga `-1` dan tahun `2030`<br>2. Simpan | HTTP 422; error pada field `purchase_price` dan `purchase_year`; data tidak tersimpan | Sesuai dengan expected | Pass | `php artisan test --filter=test_validasi_harga_dan_tahun_beli_ditolak` |
| TC-005 | Kelola Aset | Detail aset menampilkan harga & tahun | Aset `AST-LPT-001` | 1. Klik nama aset di tabel<br>2. Lihat modal detail | Baris **Harga Beli** = `Rp12.500.000`, **Tahun Beli** = `2022` | Sesuai dengan expected | Pass | `php artisan test --filter=test_detail_aset_memuat_harga_dan_tahun_beli` |

## Keterangan Seeding Dummy

Seeder dijalankan dengan `php artisan db:seed` (atau `php artisan migrate:fresh --seed` untuk kondisi bersih). Seluruh seeder memakai `updateOrInsert` berdasarkan `id`, sehingga aman dijalankan ulang.

**User — 1 ADMIN + 5 USER**

| ID | Nama | Role | Status pinjaman |
|---|---|---|---|
| 1 | Admin Aset | ADMIN | Login web admin |
| 2 | Budi Santoso | USER | Sedang meminjam (loan 6 aktif) |
| 3 | Sari Wulandari | USER | Sedang meminjam (loan 5, 7, 8 aktif) |
| 4 | Andi Pratama | USER | Sudah mengembalikan (loan 3 RETURNED) |
| 5 | Dewi Lestari | USER | Sudah mengembalikan (loan 4 RETURNED) |
| 6 | Rizky Maulana | USER | Belum pernah meminjam (0 loan) |

**Aset — 10 data, kolom lama tidak diubah, ditambah `purchase_price` & `purchase_year`**

| ID | Kode | Harga Beli | Tahun Beli | Status |
|---|---|---:|---:|---|
| 1 | AST-LPT-001 | Rp12.500.000 | 2022 | AVAILABLE |
| 2 | AST-LPT-002 | Rp18.750.000 | 2023 | BORROWED |
| 3 | AST-PRJ-001 | Rp6.200.000 | 2021 | AVAILABLE |
| 4 | AST-CAM-001 | Rp9.800.000 | 2020 | AVAILABLE |
| 5 | AST-CAM-002 | Rp14.500.000 | 2022 | BORROWED |
| 6 | AST-UKR-001 | Rp85.000.000 | 2019 | BORROWED |
| 7 | AST-UKR-002 | Rp62.000.000 | 2020 | MAINTENANCE |
| 8 | AST-VHC-001 | Rp21.000.000 | 2021 | BORROWED |
| 9 | AST-LPT-003 | Rp8.900.000 | 2024 | AVAILABLE |
| 10 | AST-PRJ-002 | Rp5.400.000 | 2023 | AVAILABLE |

**Pinjaman — 8 transaksi** (4 aktif, 4 RETURNED). Yield 2 USER sedang meminjam, 2 USER sudah kembali, 1 USER belum pernah meminjam. Aset 10 sengaja tanpa transaksi agar skenario AST-08/AST-09 tetap valid.
