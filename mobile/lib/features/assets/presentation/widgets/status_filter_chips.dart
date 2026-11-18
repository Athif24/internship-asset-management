import 'package:flutter/material.dart';

import '../../../../core/theme/app_colors.dart';
import '../../../../core/theme/app_dimensions.dart';
import '../../../../core/theme/app_text_styles.dart';
import '../../domain/entities/asset_status.dart';

/// Baris filter horizontal. Chip terakhir sengaja bisa terpotong di kanan.
class StatusFilterChips extends StatelessWidget {
  const StatusFilterChips({
    super.key,
    required this.selectedFilter,
    required this.onChanged,
  });

  /// [null] berarti "Semua".
  final AssetStatus? selectedFilter;
  final ValueChanged<AssetStatus?> onChanged;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(
        left: AppDimensions.horizontalPadding,
        top: AppDimensions.chipsTopSpacing,
      ),
      child: SingleChildScrollView(
        scrollDirection: Axis.horizontal,
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: <Widget>[
            _FilterChip(
              label: 'Semua',
              selected: selectedFilter == null,
              onTap: () => onChanged(null),
            ),
            const SizedBox(width: AppDimensions.chipSpacing),
            _FilterChip(
              label: 'Tersedia',
              selected: selectedFilter == AssetStatus.available,
              onTap: () => onChanged(AssetStatus.available),
            ),
            const SizedBox(width: AppDimensions.chipSpacing),
            _FilterChip(
              label: 'Dipinjam',
              selected: selectedFilter == AssetStatus.borrowed,
              onTap: () => onChanged(AssetStatus.borrowed),
            ),
            const SizedBox(width: AppDimensions.chipSpacing),
            _FilterChip(
              label: 'Maintenance',
              selected: selectedFilter == AssetStatus.maintenance,
              onTap: () => onChanged(AssetStatus.maintenance),
            ),
          ],
        ),
      ),
    );
  }
}

class _FilterChip extends StatelessWidget {
  const _FilterChip({
    required this.label,
    required this.selected,
    required this.onTap,
  });

  final String label;
  final bool selected;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    final BorderRadius radius = BorderRadius.circular(AppDimensions.pillRadius);

    return Material(
      color: selected ? AppColors.primary : AppColors.surface,
      borderRadius: radius,
      child: InkWell(
        onTap: onTap,
        borderRadius: radius,
        child: Container(
          height: AppDimensions.chipHeight,
          padding: const EdgeInsets.symmetric(
            horizontal: AppDimensions.chipHorizontalPadding,
          ),
          decoration: BoxDecoration(
            borderRadius: radius,
            border: selected
                ? null
                : Border.all(
                    color: AppColors.border,
                    width: AppDimensions.chipBorderWidth,
                  ),
          ),
          alignment: Alignment.center,
          child: Text(
            label,
            style: selected
                ? AppTextStyles.chipActive
                : AppTextStyles.chipInactive,
          ),
        ),
      ),
    );
  }
}
