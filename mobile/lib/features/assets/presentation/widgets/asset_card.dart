import 'package:flutter/material.dart';

import '../../domain/entities/asset.dart';

/// Placeholder untuk tim UI.
class AssetCard extends StatelessWidget {
  const AssetCard({super.key, required this.asset, this.onTap});

  final Asset asset;
  final VoidCallback? onTap;

  @override
  Widget build(BuildContext context) {
    return ListTile(
      onTap: onTap,
      title: Text(asset.name),
      subtitle: Text('${asset.assetsCode} • ${asset.status}'),
    );
  }
}
