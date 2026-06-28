import 'package:flutter/material.dart';
import 'package:flutter_sixvalley_ecommerce/features/product/controllers/product_controller.dart';
import 'package:flutter_sixvalley_ecommerce/features/product/widgets/latest_product/latest_product_widget.dart';
import 'package:flutter_sixvalley_ecommerce/common/basewidget/product_shimmer_widget.dart';
import 'package:flutter_sixvalley_ecommerce/utill/dimensions.dart';
import 'package:provider/provider.dart';

class RelatedProductWidget extends StatelessWidget {
  const RelatedProductWidget({super.key});

  @override
  Widget build(BuildContext context) {
    final size = MediaQuery.of(context).size;

    return Consumer<ProductController>(
      builder: (context, productController, child) {
        return Column(children: [
          productController.relatedProductList != null ? productController.relatedProductList!.isNotEmpty ?

          SizedBox(
            height: (productController.relatedProductList?.length ?? 0) > 5 ? size.height * 0.35 : size.height * 0.16,
            child: GridView.builder(
              clipBehavior: Clip.none,
              itemCount: productController.relatedProductList?.length,
              scrollDirection: Axis.horizontal,
              gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
                crossAxisCount: (productController.relatedProductList?.length ?? 0) > 5 ? 2 : 1,
                crossAxisSpacing: 0,
                mainAxisSpacing: 0,
                childAspectRatio: 0.42,
              ),
              itemBuilder: (context, index) {
                final int crossAxisCount = (productController.relatedProductList?.length ?? 0) > 5 ? 2 : 1;
                final columnIndex = index ~/ crossAxisCount;
                final lastColumnIndex = ((productController.relatedProductList?.length ?? 0) - 1) ~/ crossAxisCount;
                final isLastColumn = columnIndex == lastColumnIndex;

                return SizedBox(
                  height: 100,
                  child: Padding(
                      padding: EdgeInsets.only(right: isLastColumn ? Dimensions.paddingSizeDefault : 0),
                      child: LatestProductWidget(productModel: productController.relatedProductList![index])
                  ),
                );
              },
            ),
          ) : const SizedBox() :
          ProductShimmer(isHomePage: false, isEnabled: productController.relatedProductList == null),
        ]);
      },
    );
  }
}