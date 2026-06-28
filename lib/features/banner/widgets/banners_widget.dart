import 'package:carousel_slider/carousel_slider.dart';
import 'package:flutter/material.dart';
import 'package:flutter_sixvalley_ecommerce/features/banner/controllers/banner_controller.dart';
import 'package:flutter_sixvalley_ecommerce/features/banner/widgets/banner_shimmer.dart';
import 'package:flutter_sixvalley_ecommerce/utill/dimensions.dart';
import 'package:flutter_sixvalley_ecommerce/common/basewidget/custom_image_widget.dart';
import 'package:provider/provider.dart';

class BannersWidget extends StatelessWidget {
  const BannersWidget({super.key});

  @override
  Widget build(BuildContext context) {
    return Consumer<BannerController>(
        builder: (context, bannerProvider, child) {
        double width = MediaQuery.of(context).size.width;
        return Column(children: [
          bannerProvider.mainBannerList != null ? bannerProvider.mainBannerList!.isNotEmpty ?
          Column(children: [
            SizedBox(
              width: width,
              child: CarouselSlider.builder(
                options: CarouselOptions(
                    aspectRatio: 16 / 9,
                    viewportFraction: 0.92,
                    autoPlay: true,
                    autoPlayInterval: const Duration(seconds: 4),
                    autoPlayAnimationDuration: const Duration(milliseconds: 800),
                    autoPlayCurve: Curves.fastOutSlowIn,
                    enlargeCenterPage: true,
                    enlargeStrategy: CenterPageEnlargeStrategy.scale,
                    disableCenter: true,
                    onPageChanged: (index, reason) {
                      Provider.of<BannerController>(context, listen: false).setCurrentIndex(index);
                    }),
                itemCount: bannerProvider.mainBannerList!.length,
                itemBuilder: (context, index, _) {
                  return InkWell(
                    onTap: () {
                      if(bannerProvider.mainBannerList![index].resourceId != null || bannerProvider.mainBannerList![index].url != null) {
                        bannerProvider.clickBannerRedirect(context,
                            bannerProvider.mainBannerList![index].resourceId,
                            bannerProvider.mainBannerList![index].resourceType == 'product' ?
                            bannerProvider.mainBannerList![index].product : null,
                            bannerProvider.mainBannerList![index].resourceType,
                            url: bannerProvider.mainBannerList![index].url);
                      }
                    },
                    child: Container(
                      margin: const EdgeInsets.symmetric(vertical: 5),
                      decoration: BoxDecoration(
                        borderRadius: BorderRadius.circular(16),
                        boxShadow: [
                          BoxShadow(
                            color: Colors.black.withOpacity(0.12),
                            blurRadius: 12,
                            offset: const Offset(0, 4),
                          )
                        ],
                      ),
                      child: ClipRRect(
                        borderRadius: BorderRadius.circular(16),
                        child: Stack(
                          fit: StackFit.expand,
                          children: [
                            CustomImageWidget(
                              image: '${bannerProvider.mainBannerList?[index].photoFullUrl?.path}',
                              fit: BoxFit.cover,
                              width: double.infinity,
                            ),
                            Positioned.fill(
                              child: DecoratedBox(
                                decoration: BoxDecoration(
                                  gradient: LinearGradient(
                                    begin: Alignment.bottomCenter,
                                    end: Alignment.topCenter,
                                    colors: [
                                      Colors.black.withOpacity(0.3),
                                      Colors.transparent,
                                    ],
                                  ),
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                  );
                },
              ),
            ),

            const SizedBox(height: Dimensions.paddingSizeSmall),

            Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: bannerProvider.mainBannerList!.asMap().entries.map((entry) {
                bool isSelected = bannerProvider.currentIndex == entry.key;
                return AnimatedContainer(
                  duration: const Duration(milliseconds: 300),
                  width: isSelected ? 24.0 : 8.0,
                  height: 4.0,
                  margin: const EdgeInsets.symmetric(horizontal: 3.0),
                  decoration: BoxDecoration(
                    borderRadius: BorderRadius.circular(10),
                    color: isSelected 
                        ? Theme.of(context).primaryColor 
                        : Theme.of(context).primaryColor.withOpacity(0.2),
                  ),
                );
              }).toList(),
            ),
          ]) : const SizedBox.shrink() : (bannerProvider.mainBannerList == null) ? const BannerShimmer() : const SizedBox.shrink(),

        ]);
      }
    );
  }
}
