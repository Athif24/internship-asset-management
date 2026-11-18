import 'package:flutter/material.dart';

/// Placeholder untuk tim UI.
///
/// Contoh pemanggilan data:
///   `context.read<AssetController>().loadAssets(status: 'AVAILABLE');`
class AssetListPage extends StatelessWidget {
  const AssetListPage({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Daftar Aset')),
      body: const Center(
        child: Text('TODO UI: list aset + filter status/kategori + search.'),
      ),
    );
  }
}
