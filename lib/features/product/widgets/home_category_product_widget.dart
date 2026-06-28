import 'package:flutter/material.dart';
import 'package:flutter_sixvalley_ecommerce/features/product/controllers/product_controller.dart';
import 'package:flutter_sixvalley_ecommerce/features/product/widgets/home_category_item_widget.dart';
import 'package:provider/provider.dart';

class HomeCategoryProductWidget extends StatelessWidget {
  final bool isHomePage;
  const HomeCategoryProductWidget({super.key, required this.isHomePage});

  @override
  Widget build(BuildContext context) {
    return Consumer<ProductController>(
      builder: (context, homeCategoryProductController, child) {
        return homeCategoryProductController.homeCategoryProductList.isNotEmpty ?
        ListView.builder(
          itemCount: homeCategoryProductController.homeCategoryProductList.length,
          shrinkWrap: true,
          padding: EdgeInsets.zero,
          physics: const NeverScrollableScrollPhysics(),
          itemBuilder: (context, index) {
            return HomeCategoryProductItemWidget(
              homeCategoryProduct: homeCategoryProductController.homeCategoryProductList[index],
              index: index, 
              isHomePage: isHomePage,
            );
          },
        ) : const SizedBox();
      },
    );
  }
}
