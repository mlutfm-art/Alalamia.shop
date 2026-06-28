import 'package:carousel_slider/carousel_slider.dart';
import 'package:flutter/material.dart';
import 'package:flutter_sixvalley_ecommerce/common/basewidget/custom_image_widget.dart';
import 'package:flutter_sixvalley_ecommerce/features/banner/controllers/banner_controller.dart';
import 'package:flutter_sixvalley_ecommerce/features/banner/domain/models/banner_model.dart';
import 'package:flutter_sixvalley_ecommerce/utill/dimensions.dart';
import 'package:provider/provider.dart';

class FooterBannerSliderWidget extends StatelessWidget {
  const FooterBannerSliderWidget({super.key});

  @override
  Widget build(BuildContext context) {
    final double width = MediaQuery.of(context).size.width;

    return Column(children: [
      Consumer<BannerController>(
        builder: (context, bannerProvider, child) {
          List<BannerModel> bannerList = [];

          if ((bannerProvider.footerBannerList?.length ?? 0) > 0) {
            bannerList = bannerProvider.footerBannerList ?? [];
          }

          if (bannerList.isEmpty) {
            return const SizedBox();
          }

          // تم تعديل النسبة هنا لتكون 6:1 (نحيف جداً)
          // الارتفاع سيكون سدس العرض فقط
          if (bannerList.length == 1) {
            return Padding(
              padding: const EdgeInsets.symmetric(horizontal: Dimensions.paddingSizeDefault),
              child: InkWell(
                onTap: () => bannerProvider.clickBannerRedirect(
                  context,
                  bannerList[0].resourceId,
                  bannerList[0].resourceType == 'product' ? bannerList[0].product : null,
                  bannerList[0].resourceType,
                ),
                child: ClipRRect(
                  borderRadius: BorderRadius.circular(Dimensions.paddingSizeSmall),
                  child: AspectRatio(
                    aspectRatio: 6 / 1, // نسبة العرض 6 والطول 1 (نحيف جداً)
                    child: CustomImageWidget(
                      image: '${bannerList[0].photoFullUrl?.path}',
                      fit: BoxFit.cover,
                      width: width,
                    ),
                  ),
                ),
              ),
            );
          }

          return Stack(children: [
            SizedBox(
              height: width * (1 / 6), // الارتفاع أصبح سدس العرض
              width: width,
              child: CarouselSlider.builder(
                options: CarouselOptions(
                    aspectRatio: 6 / 1, // تطبيق النسبة النحيفة على السلايدر
                    viewportFraction: 1.0,
                    autoPlay: true,
                    enlargeCenterPage: false,
                    disableCenter: true,
                    onPageChanged: (index, reason) {
                      Provider.of<BannerController>(context, listen: false).onChangeFooterBannerIndex(index);
                    }),
                itemCount: bannerList.length,
                itemBuilder: (context, index, _) {
                  return InkWell(
                    onTap: () => bannerProvider.clickBannerRedirect(
                        context,
                        bannerList[index].resourceId,
                        bannerList[index].resourceType == 'product' ? bannerList[index].product : null,
                        bannerList[index].resourceType
                    ),
                    child: Padding(
                      padding: const EdgeInsets.symmetric(horizontal: Dimensions.paddingSizeExtraSmall),
                      child: ClipRRect(
                        borderRadius: BorderRadius.circular(Dimensions.paddingSizeSmall),
                        child: CustomImageWidget(
                          image: '${bannerList[index].photoFullUrl?.path}',
                          fit: BoxFit.cover,
                          width: width,
                        ),
                      ),
                    ),
                  );
                },
              ),
            ),

            if (bannerList.length > 1)
              Positioned(
                bottom: 2, // تم خفض الارتفاع ليناسب النحافة
                left: 0, right: 0,
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: bannerList.map((banner) {
                    int index = bannerList.indexOf(banner);
                    return Container(
                      width: 5, height: 5, // تصغير النقاط لتناسب الشكل النحيف
                      margin: const EdgeInsets.symmetric(horizontal: 2),
                      decoration: BoxDecoration(
                        shape: BoxShape.circle,
                        color: index == bannerProvider.footerBannerIndex
                            ? Theme.of(context).primaryColor
                            : Theme.of(context).primaryColor.withOpacity(0.2),
                      ),
                    );
                  }).toList(),
                ),
              ),
          ]);
        },
      ),
      const SizedBox(height: Dimensions.paddingSizeSmall),
    ]);
  }
}