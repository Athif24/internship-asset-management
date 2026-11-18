/// Error terstruktur hasil mapping dari envelope response backend.
///
/// [code] bisa berupa kode aturan bisnis (AST-01 ... AST-09),
/// kode validasi (VALIDATION_ERROR), atau kode jaringan (NETWORK_ERROR).
class ApiException implements Exception {
  const ApiException({
    required this.statusCode,
    required this.code,
    required this.message,
    this.detail,
  });

  final int statusCode;
  final String code;
  final String message;
  final Object? detail;

  bool get isUnauthenticated => statusCode == 401;
  bool get isForbidden => statusCode == 403;
  bool get isConflict => statusCode == 409;
  bool get isValidation => statusCode == 422;
  bool get isNetworkError => code == 'NETWORK_ERROR';

  @override
  String toString() => 'ApiException($statusCode, $code): $message';
}
