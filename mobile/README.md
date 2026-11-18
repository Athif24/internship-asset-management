# mobile

Flutter app untuk **Company Asset Management** (Tim B).

## Struktur Feature-First

```
lib/
├── main.dart                      # entry point
├── app/
│   ├── app.dart                   # root widget + injeksi provider
│   ├── config/api_config.dart     # base URL dari --dart-define
│   ├── routes/app_routes.dart
│   └── theme/app_theme.dart
├── core/
│   ├── network/
│   │   ├── api_client.dart        # parsing envelope + Bearer + error AST
│   │   ├── api_endpoints.dart
│   │   └── api_exception.dart
│   ├── storage/token_storage.dart
│   ├── error/result.dart
│   └── widgets/state_widgets.dart # loading / error / empty
└── features/
    ├── auth/    { data, domain, presentation }
    ├── assets/  { data, domain, presentation }
    ├── loans/   { data, domain, presentation }
    └── profile/ { presentation }
```

### Pembagian kerja

- **Bagian API (sudah siap):** `core/network/*`, `core/storage/*`, semua folder `data/` (datasource + model + repository impl), dan `domain/` (entity + interface repository).
- **Bagian UI (placeholder TODO):** `presentation/pages/*` dan `presentation/widgets/*`.

Controller sudah tersedia lewat `context.read<AuthController>()`, `context.read<AssetController>()`, dan `context.read<LoanController>()`.

## Menjalankan

Pastikan backend sudah jalan di `http://127.0.0.1:8000` (`php artisan serve --host=0.0.0.0 --port=8000`), lalu:

```powershell
flutter pub get

# Emulator Android
flutter run --dart-define=API_BASE_URL=http://10.0.2.2:8000/api

# Simulator iOS
flutter run --dart-define=API_BASE_URL=http://127.0.0.1:8000/api

# HP fisik (PC & HP satu WiFi, ganti IP dengan IP LAN PC)
flutter run --dart-define=API_BASE_URL=http://192.168.1.5:8000/api
```

Default `API_BASE_URL` jika tidak di-set: `http://10.0.2.2:8000/api`.

## Verifikasi API cepat

```powershell
dart run tool/api_smoke_test.dart
```

## Credential demo

- Employee: `budi@example.com` / `Password123!`
- Admin (web): `admin@example.com` / `Password123!` — tidak bisa login lewat mobile.

## Catatan penting

- Field aset di backend bernama `assets_code`, bukan `asset_code`.
- Request pinjam mengirim `asset_id`; response transaksi memakai `assets_id`.
- Endpoint `GET /api/dashboard/assets` hanya untuk ADMIN — gunakan `GET /api/assets` untuk daftar aset mobile.
- Semua error dibaca dari `error.code` (AST-01 ... AST-09, VALIDATION_ERROR, NETWORK_ERROR). Jangan tampilkan sukses saat response gagal.
