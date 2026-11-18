import 'package:flutter/material.dart';

/// Placeholder untuk tim UI.
class AssetDetailPage extends StatelessWidget {
  const AssetDetailPage({super.key, required this.assetId});

  final int assetId;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('Detail Aset #$assetId')),
      body: const Center(
        child: Text('TODO UI: detail aset + tombol Pinjam/Kembalikan.'),
      ),
    );
  }
}
