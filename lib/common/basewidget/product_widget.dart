import 'package:flutter/material.dart';
import 'package:flutter_sixvalley_ecommerce/common/basewidget/discount_tag_widget.dart';
import 'package:flutter_sixvalley_ecommerce/features/product/domain/models/product_model.dart';
import 'package:flutter_sixvalley_ecommerce/features/shop/domain/models/shop_navigation_model.dart';
import 'package:flutter_sixvalley_ecommerce/helper/price_converter.dart';
import 'package:flutter_sixvalley_ecommerce/helper/route_healper.dart';
import 'package:flutter_sixvalley_ecommerce/localization/controllers/localization_controller.dart';
import 'package:flutter_sixvalley_ecommerce/localization/language_constrants.dart';
import 'package:flutter_sixvalley_ecommerce/theme/controllers/theme_controller.dart';
import 'package:flutter_sixvalley_ecommerce/utill/custom_themes.dart';
import 'package:flutter_sixvalley_ecommerce/utill/dimensions.dart';
import 'package:flutter_sixvalley_ecommerce/common/basewidget/custom_image_widget.dart';
import 'package:flutter_sixvalley_ecommerce/features/product_details/widgets/favourite_button_widget.dart';
import 'package:provider/provider.dart';


class ProductWidget extends StatelessWidget {
  final Product productModel;
  final int productNameLine;
  final double? margin;
  final SellerNavigationModel? sellerNavigationModel;
  const ProductWidget({super.key, required this.productModel, this.productNameLine = 1, this.margin, this.sellerNavigationModel});

  @override
  Widget build(BuildContext context) {
    final bool isLtr  = Provider.of<LocalizationController>(context, listen: false).isLtr;
    double ratting = (productModel.rating?.isNotEmpty ?? false) ?  double.parse('${productModel.rating?[0].average}') : 0;

    return InkWell(
      onTap: () {
        RouterHelper.getProductDetailsRoute(
          action: RouteAction.push,
          productId: productModel.id,
          slug: productModel.slug,
        );
      },
      child: Container(
        margin: EdgeInsets.all(margin ?? Dimensions.paddingSizeExtraSmall),
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
          border: Border.all(color: Theme.of(context).primaryColor.withOpacity(0.1), width: 1),
          color: Theme.of(context).cardColor,
          boxShadow: [BoxShadow(
            color: Colors.black.withOpacity(0.05),
            spreadRadius: 0,
            blurRadius: 10,
            offset: const Offset(0, 2),
          )],
        ),
        child: Stack(children: [
          Column(crossAxisAlignment: CrossAxisAlignment.stretch, mainAxisSize: MainAxisSize.min, children: [
            // Image Section - AspectRatio solves "hasSize" errors in Masonry Grids
            AspectRatio(
              aspectRatio: 1.0,
              child: ClipRRect(
                borderRadius: const BorderRadius.only(
                  topLeft: Radius.circular(Dimensions.radiusDefault),
                  topRight: Radius.circular(Dimensions.radiusDefault),
                ),
                child: Stack(children: [
                  Container(
                    margin: const EdgeInsets.all(Dimensions.paddingSizeExtraSmall).copyWith(bottom: 0),
                    decoration: BoxDecoration(
                      borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
                      color: Theme.of(context).highlightColor,
                    ),
                    child: ClipRRect(
                      borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
                      child: CustomImageWidget(
                        image: '${productModel.thumbnailFullUrl?.path}',
                        fit: BoxFit.contain,
                        height: double.infinity,
                        width: double.infinity,
                      ),
                    ),
                  ),

                  if(productModel.currentStock! == 0 && productModel.productType == 'physical')...[
                    Positioned.fill(
                      child: Container(
                        margin: const EdgeInsets.all(Dimensions.paddingSizeExtraSmall).copyWith(bottom: 0),
                        decoration: BoxDecoration(
                          color: Colors.black.withOpacity(0.4),
                          borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
                        ),
                        child: Center(
                          child: Container(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                            decoration: BoxDecoration(
                              color: Theme.of(context).colorScheme.error.withOpacity(0.8),
                              borderRadius: BorderRadius.circular(4),
                            ),
                            child: Text(
                              getTranslated('out_of_stock', context) ?? '',
                              style: textBold.copyWith(color: Colors.white, fontSize: 10),
                              textAlign: TextAlign.center,
                            ),
                          ),
                        ),
                      ),
                    ),
                  ],
                ]),
              ),
            ),

            // Product Details - Dynamic height
            Padding(
              padding: const EdgeInsets.fromLTRB(8, 4, 8, 8),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start, 
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                if(ratting > 0) Row(mainAxisAlignment: MainAxisAlignment.start, children: [
                  const Icon(Icons.star_rate_rounded, color: Colors.orange, size: 12),
                  const SizedBox(width: 2),
                  Text(ratting.toStringAsFixed(1), style: textBold.copyWith(
                    fontSize: 10,
                    color: Theme.of(context).textTheme.bodyLarge?.color,
                  )),
                  const SizedBox(width: 2),
                  Text('(${PriceConverter.longToShortPrice(productModel.reviewCount?.toDouble() ?? 0, withDecimalPoint: false)})',
                    style: textRegular.copyWith(fontSize: 9, color: Theme.of(context).hintColor),
                  ),
                ]),

                const SizedBox(height: 2),

                Text(productModel.name ?? '', 
                  textAlign: TextAlign.start, 
                  style: textBold.copyWith(
                    fontSize: 11,
                    color: Theme.of(context).textTheme.bodyLarge?.color,
                  ), 
                  maxLines: productNameLine, 
                  overflow: TextOverflow.ellipsis
                ),

                const SizedBox(height: 4),

                if(hasDiscount())
                  Text(PriceConverter.convertPrice(context, productModel.unitPrice), style: titleRegular.copyWith(
                    color: Theme.of(context).hintColor,
                    decoration: TextDecoration.lineThrough,
                    fontSize: 9,
                  )),

                Text(
                  PriceConverter.convertPrice(
                    context, productModel.unitPrice,
                    discountType: (productModel.clearanceSale?.discountAmount ?? 0) > 0
                      ? productModel.clearanceSale?.discountType
                      : productModel.discountType,
                    discount: (productModel.clearanceSale?.discountAmount ?? 0) > 0
                      ? productModel.clearanceSale?.discountAmount
                      : productModel.discount,
                  ),
                  style: robotoBold.copyWith(
                    color: Provider.of<ThemeController>(context, listen: false).darkTheme ?
                      Theme.of(context).textTheme.bodyLarge?.color :
                      Theme.of(context).primaryColor,
                    fontSize: 12,
                  ),
                ),
              ]),
            ),
          ]),

          if(hasDiscount())
            DiscountTagWidget(productModel: productModel, positionedTop: 0, topLeftBorderRadius: Dimensions.radiusDefault, bottomRightBorderRadius: Dimensions.radiusDefault),

          Positioned(top: 10, right: isLtr ? 10 : null, left: !isLtr ? 10 : null,
            child: FavouriteButtonWidget(
              sellerNavigationModel: sellerNavigationModel,
              backgroundColor: Theme.of(context).cardColor.withOpacity(0.9),
              productId: productModel.id,
            ),
          ),
        ]),
      ),
    );
  }

  bool hasDiscount() => (productModel.discount != null && productModel.discount! > 0) || (productModel.clearanceSale?.discountAmount ?? 0) > 0;
}
