import '../network/api_exception.dart';

/// Hasil operasi ringan tanpa dependency tambahan.
class Result<T> {
  const Result._({this.data, this.error});

  final T? data;
  final ApiException? error;

  bool get isSuccess => error == null;
  bool get isFailure => !isSuccess;

  factory Result.success(T data) => Result._(data: data);
  factory Result.failure(ApiException error) => Result._(error: error);

  R fold<R>(
    R Function(T data) onSuccess,
    R Function(ApiException error) onFailure,
  ) {
    return isSuccess ? onSuccess(data as T) : onFailure(error!);
  }
}
