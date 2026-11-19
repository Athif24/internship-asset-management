import 'dart:convert';

import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';
import 'package:mobile/core/network/api_client.dart';
import 'package:mobile/core/network/api_endpoints.dart';

void main() {
  const baseUrl = 'http://192.168.106.74:8000/api';

  http.Response okResponse() => http.Response(
    jsonEncode(<String, dynamic>{
      'success': true,
      'message': 'OK',
      'data': <String, dynamic>{},
      'error': null,
    }),
    200,
    headers: <String, String>{'content-type': 'application/json'},
  );

  test('endpoint absolut tidak menggandakan base URL', () async {
    final requestedUris = <Uri>[];
    final mockClient = MockClient((http.Request request) async {
      requestedUris.add(request.url);
      return okResponse();
    });

    final endpoints = const ApiEndpoints(baseUrl);
    final client = ApiClient(baseUrl: baseUrl, client: mockClient);

    await client.post(
      endpoints.login,
      body: <String, dynamic>{
        'email': 'budi@example.com',
        'password': 'Password123!',
      },
      authenticated: false,
    );

    expect(requestedUris, hasLength(1));
    expect(
      requestedUris.single.toString(),
      'http://192.168.106.74:8000/api/login',
    );
  });

  test('path relatif tetap digabungkan dengan base URL', () async {
    final requestedUris = <Uri>[];
    final mockClient = MockClient((http.Request request) async {
      requestedUris.add(request.url);
      return okResponse();
    });

    final client = ApiClient(baseUrl: baseUrl, client: mockClient);

    await client.get('/me', authenticated: false);

    expect(requestedUris, hasLength(1));
    expect(
      requestedUris.single.toString(),
      'http://192.168.106.74:8000/api/me',
    );
  });
}
