import 'package:flutter/material.dart';
import 'package:flutter_sixvalley_ecommerce/features/product/domain/models/product_model.dart';
import 'package:flutter_sixvalley_ecommerce/helper/price_converter.dart';
import 'package:flutter_sixvalley_ecommerce/localization/controllers/localization_controller.dart';
import 'package:flutter_sixvalley_ecommerce/localization/language_constrants.dart';
import 'package:flutter_sixvalley_ecommerce/utill/custom_themes.dart';
import 'package:flutter_sixvalley_ecommerce/utill/dimensions.dart';
import 'package:flutter_sixvalley_ecommerce/common/basewidget/custom_image_widget.dart';
import 'package:flutter_sixvalley_ecommerce/features/product_details/widgets/favourite_button_widget.dart';
import 'package:flutter_sixvalley_ecommerce/utill/images.dart';
import 'package:flutter_sixvalley_ecommerce/helper/route_healper.dart';
import 'package:provider/provider.dart';

class SliderProductWidget extends StatelessWidget {
  final Product product;
  final bool isCurrentIndex;
  const SliderProductWidget({super.key, required this.product, this.isCurrentIndex = true});

  @override
  Widget build(BuildContext context) {
    bool isLtr = Provider.of<LocalizationController>(context, listen: false).isLtr;
    double ratting = (product.rating?.isNotEmpty ?? false) ?  double.parse('${product.rating?[0].average}') : 0;

    return InkWell(
      onTap: () {
        RouterHelper.getProductDetailsRoute(
          action: RouteAction.push,
          productId: product.id,
          slug: product.slug,
          fromFlashDeals: true,
        );
      },
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 500),
        margin: EdgeInsets.symmetric(
          vertical: isCurrentIndex ? 10 : 40, 
          horizontal: 8,
        ),
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(40), // حواف دائرية كبيرة متناسقة مع البنر
          color: Theme.of(context).cardColor, // خلفية بيضاء
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.05), // ظل ناعم بأسلوب ClariCare
              blurRadius: 20,
              spreadRadius: 0,
              offset: const Offset(0, 10),
            )
          ],
        ),
        child: ClipRRect(
          borderRadius: BorderRadius.circular(40),
          child: Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
            
            // Image Section (9:16 oriented)
            Expanded(
              flex: 6,
              child: Stack(children: [
                CustomImageWidget(
                  image: '${product.thumbnailFullUrl?.path}',
                  width: double.infinity,
                  height: double.infinity,
                  fit: BoxFit.cover,
                ),
                
                // Out of stock overlay
                if(product.currentStock! == 0 && product.productType == 'physical')
                  Container(
                    color: Colors.black.withOpacity(0.4),
                    child: Center(
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                        decoration: BoxDecoration(
                          color: Theme.of(context).colorScheme.error.withOpacity(0.9),
                          borderRadius: BorderRadius.circular(20),
                        ),
                        child: Text(
                          getTranslated('out_of_stock', context) ?? '',
                          style: textBold.copyWith(color: Colors.white, fontSize: 8),
                        ),
                      ),
                    ),
                  ),

                // Favorite Button
                Positioned(
                  top: 15, right: isLtr ? 15 : null, left: !isLtr ? 15 : null,
                  child: FavouriteButtonWidget(
                    backgroundColor: Theme.of(context).cardColor.withOpacity(0.7),
                    productId: product.id,
                  ),
                ),

                // Flash Icon
                Positioned(
                  top: 15, left: isLtr ? 15 : null, right: !isLtr ? 15 : null,
                  child: Container(
                    padding: const EdgeInsets.all(4),
                    decoration: BoxDecoration(
                      color: Theme.of(context).primaryColor,
                      shape: BoxShape.circle,
                    ),
                    child: Image.asset(Images.flashDeal, scale: 5, color: Colors.white),
                  ),
                ),
              ]),
            ),

            // Details Section
            Expanded(
              flex: 3,
              child: Container(
                padding: const EdgeInsets.fromLTRB(15, 5, 15, 15),
                color: Theme.of(context).cardColor,
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Text(
                      product.name ?? '',
                      textAlign: TextAlign.center,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: textBold.copyWith(fontSize: 13, height: 1.2),
                    ),
                    
                    const SizedBox(height: 5),

                    // Price Section
                    Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Text(
                          PriceConverter.convertPrice(
                            context, product.unitPrice,
                            discountType: (product.clearanceSale?.discountAmount ?? 0) > 0
                              ? product.clearanceSale?.discountType
                              : product.discountType,
                            discount: (product.clearanceSale?.discountAmount ?? 0) > 0
                              ? product.clearanceSale?.discountAmount
                              : product.discount,
                          ),
                          style: robotoBold.copyWith(
                            color: Theme.of(context).primaryColor,
                            fontSize: 15,
                          ),
                        ),
                      ],
                    ),

                    if(hasDiscount())
                      Text(
                        PriceConverter.convertPrice(context, product.unitPrice),
                        style: textRegular.copyWith(
                          color: Theme.of(context).hintColor,
                          decoration: TextDecoration.lineThrough,
                          fontSize: 11,
                        ),
                      ),
                  ],
                ),
              ),
            ),
          ]),
        ),
      ),
    );
  }

  bool hasDiscount() => (product.discount != null && product.discount! > 0) || (product.clearanceSale?.discountAmount ?? 0) > 0;
}
