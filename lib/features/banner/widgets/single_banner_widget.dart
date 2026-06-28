import 'package:flutter/material.dart';
import 'package:flutter_sixvalley_ecommerce/features/banner/controllers/banner_controller.dart';
import 'package:flutter_sixvalley_ecommerce/features/banner/domain/models/banner_model.dart';
import 'package:flutter_sixvalley_ecommerce/common/basewidget/custom_image_widget.dart';
import 'package:provider/provider.dart';

class SingleBannersWidget extends StatelessWidget {
  final BannerModel? bannerModel;
  final double? height;
  final bool noRadius;
  const SingleBannersWidget({super.key, this.bannerModel, this.height, this.noRadius = false});

  @override
  Widget build(BuildContext context) {
    return Consumer<BannerController>(
      builder: (context, footerBannerProvider, child) {
        return InkWell(
          onTap: () {
            footerBannerProvider.clickBannerRedirect(
              context,
              bannerModel?.resourceId,
              bannerModel?.resourceType == 'product' ? bannerModel?.product : null,
              bannerModel?.resourceType,
            );
          },
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 2.0),
            child: ClipRRect(
              borderRadius: BorderRadius.all(Radius.circular(noRadius ? 0 : 10)), // تم زيادة التدوير قليلاً لجمالية التصميم
              child: AspectRatio(
                // هنا الحل: تم تغيير النسبة لتصبح 4:1 (عرض 4 أضعاف الطول) ليكون نحيفاً
                aspectRatio: 4 / 1,
                child: CustomImageWidget(
                  image: '${bannerModel?.photoFullUrl?.path}',
                  fit: BoxFit.cover, // يضمن ملء الصورة للمساحة النحيفة دون تشويه
                  width: MediaQuery.of(context).size.width,
                ),
              ),
            ),
          ),
        );
      },
    );
  }
}