import 'package:flutter/material.dart';
import 'package:flutter_sixvalley_ecommerce/theme/controllers/theme_controller.dart';
import 'package:flutter_sixvalley_ecommerce/utill/dimensions.dart';
import 'package:provider/provider.dart';
import 'package:shimmer/shimmer.dart';

class CategoryShimmerWidget extends StatelessWidget {
  const CategoryShimmerWidget({super.key});

  @override
  Widget build(BuildContext context) {
    bool isDark = Provider.of<ThemeController>(context, listen: false).darkTheme;
    Color baseColor = isDark ? Colors.grey[800]! : Colors.grey[300]!;
    Color highlightColor = isDark ? Colors.grey[700]! : Colors.grey[100]!;

    return SizedBox(
      height: 110,
      child: ListView.builder(
        itemCount: 6,
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsets.symmetric(horizontal: Dimensions.homePagePadding),
        itemBuilder: (context, index) {
          return Padding(
            padding: const EdgeInsets.only(right: 15),
            child: Shimmer.fromColors(
              baseColor: baseColor,
              highlightColor: highlightColor,
              child: Column(
                children: [
                  Container(
                    height: 75, width: 75,
                    decoration: const BoxDecoration(
                      color: Colors.white,
                      shape: BoxShape.circle,
                    ),
                  ),
                  const SizedBox(height: 8),
                  Container(
                    height: 10, width: 45,
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(5),
                    ),
                  ),
                ],
              ),
            ),
          );
        },
      ),
    );
  }
}
