/// Konfigurasi alamat API.
///
/// Cara ganti base URL tanpa mengubah kode:
///   flutter run --dart-define=API_BASE_URL=http://10.0.2.2:8000/api   (emulator Android)
///   flutter run --dart-define=API_BASE_URL=http://127.0.0.1:8000/api  (simulator iOS)
///   flutter run --dart-define=API_BASE_URL=http://192.168.1.5:8000/api (HP fisik, IP LAN PC)
class ApiConfig {
  const ApiConfig({required this.baseUrl});

  final String baseUrl;

  factory ApiConfig.fromEnvironment() {
    const url = String.fromEnvironment(
      'API_BASE_URL',
      defaultValue: 'http://10.0.2.2:8000/api',
    );
    return ApiConfig(baseUrl: url);
  }
}
