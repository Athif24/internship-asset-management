import 'package:flutter/material.dart';

import '../../../../../core/theme/app_colors.dart';
import '../../../../../core/theme/app_dimensions.dart';
import '../../../domain/entities/asset_status.dart';
import 'info_row.dart';

/// Format harga tanpa spasi, mis. 12500000 -> Rp12.500.000.
String formatPurchasePrice(num? value) {
  if (value == null) {
    return '-';
  }

  final String digits = value.round().toString();
  final StringBuffer buffer = StringBuffer('Rp');
  for (int i = 0; i < digits.length; i++) {
    if (i > 0 && (digits.length - i) % 3 == 0) {
      buffer.write('.');
    }
    buffer.write(digits[i]);
  }
  return buffer.toString();
}

/// Kartu putih berisi informasi detail aset dengan divider di antaranya.
class InfoCard extends StatelessWidget {
  const InfoCard({
    super.key,
    required this.code,
    required this.category,
    required this.status,
    this.purchasePrice,
    this.purchaseYear,
  });

  final String code;
  final String category;
  final AssetStatus status;
  final double? purchasePrice;
  final int? purchaseYear;

  @override
  Widget build(BuildContext context) {
    final String price = formatPurchasePrice(purchasePrice);
    final String year = purchaseYear?.toString() ?? '-';

    return Container(
      padding: const EdgeInsets.symmetric(
        horizontal: AppDimensions.detailInfoCardHorizontalPadding,
        vertical: AppDimensions.detailInfoCardVerticalPadding,
      ),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(AppDimensions.detailInfoCardRadius),
        border: Border.all(
          color: AppColors.detailInfoBorder,
          width: AppDimensions.chipBorderWidth,
        ),
      ),
      child: Column(
        children: <Widget>[
          InfoRow(label: 'Kode', value: code),
          const Divider(
            height: AppDimensions.detailDividerHeight,
            thickness: AppDimensions.detailDividerHeight,
            color: AppColors.detailDivider,
          ),
          InfoRow(label: 'Kategori', value: category),
          const Divider(
            height: AppDimensions.detailDividerHeight,
            thickness: AppDimensions.detailDividerHeight,
            color: AppColors.detailDivider,
          ),
          InfoRow(label: 'Status', value: status.name.toUpperCase()),
          const Divider(
            height: AppDimensions.detailDividerHeight,
            thickness: AppDimensions.detailDividerHeight,
            color: AppColors.detailDivider,
          ),
          InfoRow(label: 'Harga Beli', value: price),
          const Divider(
            height: AppDimensions.detailDividerHeight,
            thickness: AppDimensions.detailDividerHeight,
            color: AppColors.detailDivider,
          ),
          InfoRow(label: 'Tahun Beli', value: year),
        ],
      ),
    );
  }
}
