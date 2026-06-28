import 'package:flutter/material.dart';
import 'package:flutter_sixvalley_ecommerce/features/product_details/domain/models/product_details_model.dart';
import 'package:flutter_sixvalley_ecommerce/features/product_details/widgets/cart_bottom_sheet_widget.dart';
import 'package:flutter_sixvalley_ecommerce/features/splash/controllers/splash_controller.dart';
import 'package:flutter_sixvalley_ecommerce/helper/responsive_helper.dart';
import 'package:flutter_sixvalley_ecommerce/helper/route_healper.dart';
import 'package:flutter_sixvalley_ecommerce/helper/shop_helper.dart';
import 'package:flutter_sixvalley_ecommerce/localization/language_constrants.dart';
import 'package:flutter_sixvalley_ecommerce/features/cart/controllers/cart_controller.dart';
import 'package:flutter_sixvalley_ecommerce/theme/controllers/theme_controller.dart';
import 'package:flutter_sixvalley_ecommerce/utill/custom_themes.dart';
import 'package:flutter_sixvalley_ecommerce/utill/dimensions.dart';
import 'package:flutter_sixvalley_ecommerce/utill/images.dart';
import 'package:flutter_sixvalley_ecommerce/common/basewidget/show_custom_snakbar_widget.dart';
import 'package:provider/provider.dart';

class BottomCartWidget extends StatefulWidget {
  final ProductDetailsModel? product;
  const BottomCartWidget({super.key, required this.product});

  @override
  State<BottomCartWidget> createState() => _BottomCartWidgetState();
}

class _BottomCartWidgetState extends State<BottomCartWidget> {
  bool vacationIsOn = false;
  bool temporaryClose = false;

  @override
  void initState() {
    super.initState();

    vacationIsOn = ShopHelper.isVacationActive(
      context,
      startDate: widget.product?.seller?.shop?.vacationStartDate,
      endDate: widget.product?.seller?.shop?.vacationEndDate,
      vacationDurationType: widget.product?.seller?.shop?.vacationDurationType,
      vacationStatus: widget.product?.seller?.shop?.vacationStatus,
      isInHouseSeller: widget.product?.addedBy == 'admin',
    );


    if(widget.product?.addedBy == 'admin') {
      if(widget.product != null && (Provider.of<SplashController>(context, listen: false).configModel?.inhouseTemporaryClose?.status ?? false)){
        temporaryClose = true;
      }else{
        temporaryClose = false;
      }
    } else {
      if(widget.product != null && widget.product!.seller != null && widget.product!.seller!.shop!.temporaryClose!){
        temporaryClose = true;
      }else{
        temporaryClose = false;
      }
    }
  }


  @override
  Widget build(BuildContext context) {
    return Container(
      height: 85, // طول محسّن لمظهر أفخم
      padding: const EdgeInsets.symmetric(horizontal: Dimensions.paddingSizeDefault, vertical: Dimensions.paddingSizeSmall),
      decoration: BoxDecoration(
        color: Theme.of(context).highlightColor,
        borderRadius: const BorderRadius.only(
          topLeft: Radius.circular(Dimensions.radiusLarge), 
          topRight: Radius.circular(Dimensions.radiusLarge),
        ),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.05),
            blurRadius: 20,
            spreadRadius: 2,
            offset: const Offset(0, -5),
          )
        ],
      ),
      child: Row(children: [
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: Dimensions.paddingSizeExtraSmall),
          child: Stack(clipBehavior: Clip.none, children: [
            InkWell(
              onTap: () => RouterHelper.getCartScreenRoute(action: RouteAction.push),
              child: Image.asset(Images.cartArrowDownImage, 
                color: Theme.of(context).textTheme.bodyLarge?.color,
                width: 30, height: 30,
              ),
            ),
            Positioned(
              top: -5, right: -5,
              child: Consumer<CartController>(builder: (context, cart, child) {
                return Container(
                  height: 18, width: 18,
                  alignment: Alignment.center,
                  decoration: BoxDecoration(
                    shape: BoxShape.circle, 
                    color: Theme.of(context).primaryColor,
                    border: Border.all(color: Colors.white, width: 1.5),
                  ),
                  child: Text(
                    cart.cartList.length.toString(),
                    style: textBold.copyWith(fontSize: 10, color: Colors.white),
                  ),
                );
              }),
            ),
          ]),
        ),
        
        const SizedBox(width: Dimensions.paddingSizeDefault),

        Expanded(child: InkWell(
          onTap: () {
            if(vacationIsOn || temporaryClose ) {
              showCustomSnackBarWidget(getTranslated('this_shop_is_close_now', context), context, snackBarType: SnackBarType.error);
            }else{
              showModalBottomSheet(context: context, isScrollControlled: true,
                backgroundColor: Colors.transparent,
                builder: (con) => CartBottomSheetWidget(product: widget.product, callback: (){
                  showCustomSnackBarWidget(getTranslated('added_to_cart', context), context, snackBarType: SnackBarType.success);
                },)
              );
            }
          },
          child: Container(
            height: 52, // زيادة طول الزر
            alignment: Alignment.center,
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
              color: Theme.of(context).primaryColor,
              boxShadow: [
                BoxShadow(
                  color: Theme.of(context).primaryColor.withOpacity(0.3),
                  blurRadius: 12,
                  offset: const Offset(0, 4),
                )
              ],
            ),
            child: Text(
              getTranslated('add_to_cart', context)!,
              style: textBold.copyWith(
                fontSize: Dimensions.fontSizeLarge,
                color: Colors.white,
              ),
            ),
          ),
        )),
      ]),
    );
  }
}
