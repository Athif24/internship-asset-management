import 'dart:convert';
import 'dart:io';

import 'package:http/http.dart' as http;

/// Verifikasi cepat bahwa backend siap dipakai mobile.
///
/// Jalankan:
///   dart run tool/api_smoke_test.dart
///   dart run --dart-define=API_BASE_URL=http://10.0.2.2:8000/api tool/api_smoke_test.dart
const baseUrl = String.fromEnvironment(
  'API_BASE_URL',
  defaultValue: 'http://127.0.0.1:8000/api',
);

Future<void> main() async {
  try {
    final loginResponse = await http.post(
      Uri.parse('$baseUrl/login'),
      headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
      body: jsonEncode({
        'email': 'budi@example.com',
        'password': 'Password123!',
      }),
    );

    final login = jsonDecode(loginResponse.body) as Map<String, dynamic>;
    if (loginResponse.statusCode != 200 || login['success'] != true) {
      stdout.writeln('LOGIN GAGAL: ${loginResponse.statusCode} ${loginResponse.body}');
      exit(1);
    }

    final token = login['data']['token'] as String;
    stdout.writeln('LOGIN OK: token=${token.substring(0, 10)}...');

    final assetsResponse = await http.get(
      Uri.parse('$baseUrl/assets').replace(queryParameters: {'status': 'AVAILABLE'}),
      headers: {'Authorization': 'Bearer $token', 'Accept': 'application/json'},
    );
    final assets = jsonDecode(assetsResponse.body) as Map<String, dynamic>;

    if (assetsResponse.statusCode != 200 || assets['success'] != true) {
      stdout.writeln('AMBIL ASET GAGAL: ${assetsResponse.statusCode} ${assetsResponse.body}');
      exit(1);
    }

    final total = assets['data']['total'] as int;
    final first = (assets['data']['assets'] as List).first as Map<String, dynamic>;

    stdout.writeln('ASET AVAILABLE: $total');
    stdout.writeln('CONTOH: ${first['assets_code']} - ${first['name']}');
  } on SocketException catch (e) {
    stdout.writeln('JARINGAN GAGAL: ${e.message}');
    exit(1);
  }
}
