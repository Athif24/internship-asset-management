import 'dart:convert';
import 'dart:io';

import 'package:http/http.dart' as http;

import '../storage/token_storage.dart';
import 'api_exception.dart';

/// Client REST yang mem-parsing envelope standar tim:
///   { "success": true, "message": "...", "data": {...}, "error": null }
///
/// Semua kegagalan dilempar sebagai [ApiException] agar tidak pernah
/// menghasilkan "sukses palsu" (AST-09).
class ApiClient {
  ApiClient({
    required this.baseUrl,
    http.Client? client,
    TokenStorage? tokenStorage,
  })  : _client = client ?? http.Client(),
        _tokenStorage = tokenStorage ?? TokenStorage();

  final String baseUrl;
  final http.Client _client;
  final TokenStorage _tokenStorage;

  Future<dynamic> get(
    String path, {
    Map<String, String>? query,
    bool authenticated = true,
  }) {
    return _request('GET', path, query: query, authenticated: authenticated);
  }

  Future<dynamic> post(
    String path, {
    Object? body,
    bool authenticated = true,
  }) {
    return _request('POST', path, body: body, authenticated: authenticated);
  }

  Future<dynamic> delete(String path, {bool authenticated = true}) {
    return _request('DELETE', path, authenticated: authenticated);
  }

  Future<dynamic> _request(
    String method,
    String path, {
    Map<String, String>? query,
    Object? body,
    bool authenticated = true,
  }) async {
    final uri = Uri.parse('$baseUrl$path').replace(queryParameters: query);
    final headers = <String, String>{'Accept': 'application/json'};

    if (body != null) {
      headers['Content-Type'] = 'application/json';
    }

    if (authenticated) {
      final token = await _tokenStorage.readToken();
      if (token != null && token.isNotEmpty) {
        headers['Authorization'] = 'Bearer $token';
      }
    }

    final http.Response response;
    try {
      switch (method) {
        case 'GET':
          response = await _client.get(uri, headers: headers);
          break;
        case 'POST':
          response = await _client.post(
            uri,
            headers: headers,
            body: body == null ? null : jsonEncode(body),
          );
          break;
        case 'DELETE':
          response = await _client.delete(uri, headers: headers);
          break;
        default:
          throw ApiException(
            statusCode: 0,
            code: 'UNSUPPORTED_METHOD',
            message: 'Metode HTTP tidak didukung: $method',
          );
      }
    } on SocketException catch (e) {
      throw ApiException(
        statusCode: 0,
        code: 'NETWORK_ERROR',
        message: 'Tidak dapat terhubung ke server. Periksa koneksi dan alamat API.',
        detail: e.message,
      );
    } on http.ClientException catch (e) {
      throw ApiException(
        statusCode: 0,
        code: 'NETWORK_ERROR',
        message: 'Gagal menghubungi server: ${e.message}',
      );
    }

    final Map<String, dynamic> payload;
    try {
      final decoded = jsonDecode(response.body);
      if (decoded is! Map<String, dynamic>) {
        throw const FormatException('Response bukan objek JSON.');
      }
      payload = decoded;
    } on FormatException {
      throw ApiException(
        statusCode: response.statusCode,
        code: 'INVALID_RESPONSE',
        message: 'Respons server tidak valid.',
      );
    }

    final isHttpSuccess =
        response.statusCode >= 200 && response.statusCode < 300;
    final isEnvelopeSuccess = payload['success'] == true;

    if (!isHttpSuccess || !isEnvelopeSuccess) {
      final error = payload['error'];
      final errorMap = error is Map<String, dynamic> ? error : null;

      throw ApiException(
        statusCode: response.statusCode,
        code: errorMap?['code'] as String? ?? 'HTTP_${response.statusCode}',
        message: payload['message'] as String? ?? 'Permintaan gagal.',
        detail: errorMap?['detail'],
      );
    }

    return payload['data'];
  }
}
