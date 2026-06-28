import 'package:carousel_slider/carousel_slider.dart';
import 'package:flutter/material.dart';
import 'package:flutter_sixvalley_ecommerce/helper/responsive_helper.dart';
import 'package:flutter_sixvalley_ecommerce/theme/controllers/theme_controller.dart';
import 'package:flutter_sixvalley_ecommerce/utill/dimensions.dart';
import 'package:provider/provider.dart';
import 'package:shimmer/shimmer.dart';

class FlashDealShimmer extends StatelessWidget {
  const FlashDealShimmer({super.key});

  @override
  Widget build(BuildContext context) {
    bool isDark = Provider.of<ThemeController>(context, listen: false).darkTheme;
    bool isTab = ResponsiveHelper.isTab(context);
    
    Color baseColor = isDark ? Colors.grey[800]! : Colors.grey[300]!;
    Color highlightColor = isDark ? Colors.grey[700]! : Colors.grey[100]!;
    Color cardColor = isDark ? Theme.of(context).cardColor : Colors.white;

    return Padding(
      padding: const EdgeInsets.symmetric(vertical: Dimensions.paddingSizeDefault),
      child: Column(
        children: [
          // تم إزالة شيمر البنرات والتصنيفات من هنا لأنها موجودة بالفعل في أقسامها الخاصة
          // سنبقي فقط على شيمر عروض الفلاش

          // 1. شريط العنوان والمؤقت
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: Dimensions.homePagePadding),
            child: Shimmer.fromColors(
              baseColor: baseColor,
              highlightColor: highlightColor,
              child: Row(
                children: [
                  Container(height: 22, width: 140, decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(5))),
                  const Spacer(),
                  Row(
                    children: List.generate(3, (index) => Container(
                      margin: const EdgeInsets.only(left: 5),
                      height: 25, width: 25, 
                      decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(5)),
                    )),
                  )
                ],
              ),
            ),
          ),

          const SizedBox(height: Dimensions.paddingSizeDefault),

          // 2. سلايدر عروض المنتجات
          CarouselSlider.builder(
            options: CarouselOptions(
              height: isTab ? 460 : 300,
              viewportFraction: isTab ? 0.4 : 0.68,
              enlargeCenterPage: true,
              disableCenter: true,
              autoPlay: false,
              scrollPhysics: const NeverScrollableScrollPhysics(),
            ),
            itemCount: 3,
            itemBuilder: (context, index, _) {
              return Container(
                margin: const EdgeInsets.symmetric(vertical: 5, horizontal: 8),
                decoration: BoxDecoration(
                  color: cardColor,
                  borderRadius: BorderRadius.circular(Dimensions.radiusLarge),
                  boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.05), blurRadius: 10, offset: const Offset(0, 5))],
                ),
                child: Shimmer.fromColors(
                  baseColor: baseColor,
                  highlightColor: highlightColor,
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      Expanded(
                        flex: 6,
                        child: Container(
                          decoration: const BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.vertical(top: Radius.circular(Dimensions.radiusLarge)),
                          ),
                        ),
                      ),
                      Expanded(
                        flex: 4,
                        child: Padding(
                          padding: const EdgeInsets.all(Dimensions.paddingSizeSmall),
                          child: Column(
                            mainAxisAlignment: MainAxisAlignment.spaceAround,
                            children: [
                              Container(height: 10, width: double.infinity, decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(5))),
                              Row(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: List.generate(5, (index) => const Padding(
                                  padding: EdgeInsets.symmetric(horizontal: 1),
                                  child: Icon(Icons.star, size: 12, color: Colors.white),
                                )),
                              ),
                              Container(height: 18, width: 80, decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(5))),
                            ],
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              );
            },
          ),
        ],
      ),
    );
  }
}
