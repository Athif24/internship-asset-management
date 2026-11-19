import 'package:flutter/material.dart';

import '../../../../core/theme/app_colors.dart';
import '../../../../core/theme/app_dimensions.dart';
import '../../../../core/theme/app_text_styles.dart';
import '../../../auth/presentation/ui_state/auth_scope.dart';
import '../../../profile/presentation/utils/profile_initials.dart';

/// Sapaan, judul halaman, dan avatar inisial mengikuti user yang sedang login.
class AssetHeader extends StatelessWidget {
  const AssetHeader({super.key});

  @override
  Widget build(BuildContext context) {
    final auth = authControllerOf(context);

    return AnimatedBuilder(
      animation: auth,
      builder: (BuildContext context, Widget? child) {
        final user = auth.user;
        final String name = (user?.name ?? '').trim();
        final String firstName = name.isEmpty
            ? 'Pengguna'
            : name.split(RegExp(r'\s+')).first;
        final String initials = name.isEmpty ? '?' : profileInitials(name);

        return Padding(
          padding: const EdgeInsets.fromLTRB(
            AppDimensions.horizontalPadding,
            AppDimensions.headerTopPadding,
            AppDimensions.horizontalPadding,
            0,
          ),
          child: Row(
            children: <Widget>[
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisSize: MainAxisSize.min,
                  children: <Widget>[
                    Text('Halo, $firstName', style: AppTextStyles.greeting),
                    SizedBox(height: AppDimensions.headerTextSpacing),
                    Text('Daftar Aset', style: AppTextStyles.pageTitle),
                  ],
                ),
              ),
              CircleAvatar(
                radius: AppDimensions.avatarDiameter / 2,
                backgroundColor: AppColors.avatarBackground,
                child: Text(initials, style: AppTextStyles.avatar),
              ),
            ],
          ),
        );
      },
    );
  }
}
