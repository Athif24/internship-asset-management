import 'package:flutter/foundation.dart';

import '../../../../core/network/api_exception.dart';
import '../domain/entities/asset.dart';
import '../domain/entities/asset_category.dart';
import '../domain/repositories/asset_repository.dart';

class AssetController extends ChangeNotifier {
  AssetController(this._repository);

  final AssetRepository _repository;

  List<Asset> _assets = const [];
  List<AssetCategory> _categories = const [];
  Asset? _selected;
  ApiException? _error;
  bool _loading = false;

  List<Asset> get assets => _assets;
  List<AssetCategory> get categories => _categories;
  Asset? get selected => _selected;
  ApiException? get error => _error;
  bool get loading => _loading;

  Future<void> loadAssets({
    String? status,
    int? categoryId,
    String? query,
  }) async {
    _loading = true;
    _error = null;
    notifyListeners();

    try {
      _assets = await _repository.getAssets(
        status: status,
        categoryId: categoryId,
        query: query,
      );
    } on ApiException catch (e) {
      _error = e;
      _assets = const [];
    } finally {
      _loading = false;
      notifyListeners();
    }
  }

  Future<void> loadDetail(int id) async {
    _loading = true;
    _error = null;
    notifyListeners();

    try {
      _selected = await _repository.getAsset(id);
    } on ApiException catch (e) {
      _error = e;
    } finally {
      _loading = false;
      notifyListeners();
    }
  }

  Future<void> loadCategories() async {
    try {
      _categories = await _repository.getCategories();
      notifyListeners();
    } on ApiException catch (e) {
      _error = e;
      notifyListeners();
    }
  }
}
