import 'package:flutter/material.dart';
import 'package:flutter_sixvalley_ecommerce/common/basewidget/custom_image_widget.dart';
import 'package:flutter_sixvalley_ecommerce/features/order/domain/models/order_model.dart';
import 'package:flutter_sixvalley_ecommerce/features/splash/controllers/splash_controller.dart';
import 'package:flutter_sixvalley_ecommerce/helper/date_converter.dart';
import 'package:flutter_sixvalley_ecommerce/helper/price_converter.dart';
import 'package:flutter_sixvalley_ecommerce/helper/route_healper.dart';
import 'package:flutter_sixvalley_ecommerce/localization/language_constrants.dart';
import 'package:flutter_sixvalley_ecommerce/utill/custom_themes.dart';
import 'package:flutter_sixvalley_ecommerce/utill/dimensions.dart';
import 'package:flutter_sixvalley_ecommerce/utill/images.dart';
import 'package:provider/provider.dart';

class OrderWidget extends StatelessWidget {
  final Orders? orderModel;
  const OrderWidget({super.key, this.orderModel});

  @override
  Widget build(BuildContext context) {
    double orderAmount = 0;
    if (orderModel?.orderType == 'POS') {
      double itemsPrice = 0;
      double discount = 0;
      double tax = orderModel?.totalTaxAmount ?? 0;
      double coupon = orderModel?.discountAmount ?? 0;
      double shipping = orderModel?.shippingCost ?? 0;
      double eeDiscount = 0;

      if (orderModel?.details != null && orderModel!.details!.isNotEmpty) {
        for (var orderDetails in orderModel!.details!) {
          itemsPrice = itemsPrice + (orderDetails.price! * orderDetails.qty!);
          discount = discount + orderDetails.discount!;
        }
        if (orderModel!.extraDiscountType == 'percent') {
          eeDiscount = itemsPrice * (orderModel!.extraDiscount! / 100);
        } else {
          eeDiscount = orderModel!.extraDiscount ?? 0;
        }
      }
      orderAmount = (itemsPrice + tax - discount) + shipping - coupon - eeDiscount;
    }

    // منطق جلب الصورة: محاولة جلب صورة المنتج أولاً ثم صورة المتجر
    String? imageUrl;
    // 1. محاولة جلب صورة المنتج من الحقول المباشرة في الطلب (إذا أرسلتها الـ API)
    imageUrl = orderModel?.thumbnailFullUrl?.path;

    // 2. محاولة جلب الصورة من تفاصيل المنتجات (إذا توفرت)
    if (imageUrl == null || imageUrl.isEmpty) {
      if (orderModel?.details != null && orderModel!.details!.isNotEmpty) {
        imageUrl = orderModel!.details![0].product?.thumbnailFullUrl?.path ??
                   orderModel!.details![0].thumbnailFullUrl?.path;
      }
    }

    // 3. محاولة جلب صورة المتجر (الخيار الأكثر توفراً في قائمة الطلبات)
    if (imageUrl == null || imageUrl.isEmpty) {
      imageUrl = orderModel?.sellerIs == 'admin'
          ? Provider.of<SplashController>(context, listen: false).configModel?.inHouseShop?.imageFullUrl?.path
          : orderModel?.seller?.shop?.imageFullUrl?.path;
    }

    return InkWell(
      onTap: () => RouterHelper.getOrderDetailsScreenRoute(action: RouteAction.push, orderId: orderModel!.id!),
      child: Container(
        margin: const EdgeInsets.only(bottom: Dimensions.paddingSizeSmall, left: Dimensions.paddingSizeSmall, right: Dimensions.paddingSizeSmall),
        padding: const EdgeInsets.all(Dimensions.paddingSizeSmall),
        decoration: BoxDecoration(
          color: Theme.of(context).cardColor,
          borderRadius: BorderRadius.circular(10),
          boxShadow: [BoxShadow(color: Theme.of(context).primaryColor.withValues(alpha: .05), spreadRadius: 1, blurRadius: 5)],
        ),
        child: Row(children: [
          Container(
            width: 70, height: 70,
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(10),
              border: Border.all(width: 1, color: Theme.of(context).primaryColor.withValues(alpha: .1)),
            ),
            child: ClipRRect(
              borderRadius: BorderRadius.circular(10),
              child: CustomImageWidget(
                image: imageUrl ?? '', width: 70, height: 70, fit: BoxFit.cover,
                placeholder: Images.placeholder,
              ),
            ),
          ),
          const SizedBox(width: Dimensions.paddingSizeSmall),

          Expanded(
            child: Column(crossAxisAlignment: CrossAxisAlignment.start, mainAxisSize: MainAxisSize.min, children: [
              Row(children: [
                Expanded(child: Text(
                  '${getTranslated('order', context)!} #${orderModel!.id}',
                  style: textBold.copyWith(fontSize: Dimensions.fontSizeDefault),
                  maxLines: 1, overflow: TextOverflow.ellipsis,
                )),
                const SizedBox(width: Dimensions.paddingSizeExtraSmall),
                
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                  decoration: BoxDecoration(
                    color: _getStatusBgColor(context, orderModel!.orderStatus),
                    borderRadius: BorderRadius.circular(50),
                  ),
                  child: Text(
                    getTranslated(orderModel!.orderStatus, context) ?? '',
                    style: textBold.copyWith(fontSize: Dimensions.fontSizeExtraSmall - 1, color: _getStatusTextColor(context, orderModel!.orderStatus)),
                  ),
                ),
              ]),
              const SizedBox(height: 2),

              Text(
                DateConverter.localDateToIsoStringAMPMOrder(DateTime.parse(orderModel!.createdAt!)),
                style: textRegular.copyWith(fontSize: Dimensions.fontSizeSmall, color: Theme.of(context).hintColor),
              ),
              const SizedBox(height: 2),

              Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
                Text(
                  '${orderModel?.orderDetailsCount ?? 0} ${getTranslated('items', context)}',
                  style: textMedium.copyWith(fontSize: Dimensions.fontSizeSmall, color: Theme.of(context).primaryColor),
                ),

                Text(
                  PriceConverter.convertPrice(context, orderModel!.orderType == 'POS' ? orderAmount : orderModel!.orderAmount),
                  style: textBold.copyWith(fontSize: Dimensions.fontSizeDefault, color: Theme.of(context).primaryColor),
                ),
              ]),
            ]),
          ),
        ]),
      ),
    );
  }

  Color _getStatusBgColor(BuildContext context, String? status) {
    switch (status) {
      case 'delivered': return Colors.green.withValues(alpha: .1);
      case 'pending': return Colors.orange.withValues(alpha: .1);
      case 'processing': return Colors.blue.withValues(alpha: .1);
      case 'canceled':
      case 'failed': return Colors.red.withValues(alpha: .1);
      default: return Theme.of(context).primaryColor.withValues(alpha: .1);
    }
  }

  Color _getStatusTextColor(BuildContext context, String? status) {
    switch (status) {
      case 'delivered': return Colors.green;
      case 'pending': return Colors.orange;
      case 'processing': return Colors.blue;
      case 'canceled':
      case 'failed': return Colors.red;
      default: return Theme.of(context).primaryColor;
    }
  }
}
