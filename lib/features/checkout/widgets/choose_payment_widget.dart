import 'package:flutter/material.dart';
import 'package:flutter_sixvalley_ecommerce/common/basewidget/custom_asset_image_widget.dart';
import 'package:flutter_sixvalley_ecommerce/features/checkout/controllers/checkout_controller.dart';
import 'package:flutter_sixvalley_ecommerce/localization/language_constrants.dart';
import 'package:flutter_sixvalley_ecommerce/features/splash/controllers/splash_controller.dart';
import 'package:flutter_sixvalley_ecommerce/utill/custom_themes.dart';
import 'package:flutter_sixvalley_ecommerce/utill/dimensions.dart';
import 'package:flutter_sixvalley_ecommerce/utill/images.dart';
import 'package:flutter_sixvalley_ecommerce/common/basewidget/custom_image_widget.dart';
import 'package:flutter_sixvalley_ecommerce/features/auth/controllers/auth_controller.dart';
import 'package:provider/provider.dart';

class ChoosePaymentWidget extends StatelessWidget {
  final bool onlyDigital;
  const ChoosePaymentWidget({super.key, required this.onlyDigital});

  @override
  Widget build(BuildContext context) {
    return Consumer<CheckoutController>(
      builder: (context, orderProvider, _) {
        return Consumer<SplashController>(
          builder: (context, configProvider, _) {
            final configModel = configProvider.configModel;
            bool isLoggedIn = Provider.of<AuthController>(context, listen: false).isLoggedIn();

            return Container(
              padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
              decoration: BoxDecoration(
                color: Theme.of(context).cardColor,
                boxShadow: [BoxShadow(color: Theme.of(context).hintColor.withValues(alpha: 0.2), spreadRadius: 3, blurRadius: 3)],
              ),
              child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                Row(mainAxisAlignment: MainAxisAlignment.start, children: [
                  CustomAssetImageWidget(Images.paymentMethodSelectIcon, height: 18, width: 18),
                  const SizedBox(width: Dimensions.paddingSizeSmall),
                  Text(
                    getTranslated('payment_method', context) ?? '',
                    style: textMedium.copyWith(fontSize: 12.0), // خفض بنسبة 20%
                  ),
                ]),
                const SizedBox(height: Dimensions.paddingSizeDefault),

                // Cash on Delivery
                if (configModel?.cashOnDelivery ?? false)
                  _PaymentItem(
                    title: getTranslated('cash_on_delivery', context) ?? '',
                    icon: Images.cod,
                    isSelected: orderProvider.isCODChecked,
                    onTap: () => orderProvider.setOfflineChecked('cod'),
                  ),

                // Wallet Payment
                if (configModel?.walletStatus == 1 && isLoggedIn)
                  _PaymentItem(
                    title: getTranslated('wallet_payment', context) ?? '',
                    icon: Images.payWallet,
                    isSelected: orderProvider.isWalletChecked,
                    onTap: () => orderProvider.setOfflineChecked('wallet'),
                  ),

                // Offline Payment
                if (configModel?.offlinePayment != null && (orderProvider.offlinePaymentModel?.offlineMethods?.isNotEmpty ?? false))
                  _PaymentItem(
                    title: getTranslated('offline_payment', context) ?? '',
                    icon: Images.offlinePay,
                    isSelected: orderProvider.isOfflineChecked,
                    onTap: () => orderProvider.setOfflineChecked('offline'),
                  ),

                // Digital Payment Methods
                if (configModel?.digitalPayment ?? false && (configModel?.paymentMethods?.isNotEmpty ?? false))
                  ...configModel!.paymentMethods!.asMap().entries.map((entry) {
                    int index = entry.key;
                    var method = entry.value;
                    return _PaymentItem(
                      title: method.additionalDatas?.gatewayTitle ?? '',
                      imageUrl: '${configModel.paymentMethodImagePath}/${method.additionalDatas?.gatewayImage ?? ''}',
                      isSelected: orderProvider.paymentMethodIndex == index,
                      onTap: () => orderProvider.setDigitalPaymentMethodName(index, method.keyName!),
                    );
                  }),
              ]),
            );
          },
        );
      },
    );
  }
}

class _PaymentItem extends StatelessWidget {
  final String title;
  final String? icon;
  final String? imageUrl;
  final bool isSelected;
  final VoidCallback onTap;

  const _PaymentItem({
    required this.title,
    this.icon,
    this.imageUrl,
    required this.isSelected,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      child: Container(
        margin: const EdgeInsets.only(bottom: Dimensions.paddingSizeSmall),
        padding: const EdgeInsets.symmetric(horizontal: Dimensions.paddingSizeSmall, vertical: Dimensions.paddingSizeExtraSmall),
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
          border: Border.all(color: isSelected ? Theme.of(context).primaryColor : Theme.of(context).hintColor.withValues(alpha: 0.2)),
          color: isSelected ? Theme.of(context).primaryColor.withValues(alpha: 0.05) : Colors.transparent,
        ),
        child: Row(children: [
          if (imageUrl != null)
            CustomImageWidget(image: imageUrl!, height: 30, width: 30)
          else if (icon != null)
            Image.asset(icon!, height: 30, width: 30),
          
          const SizedBox(width: Dimensions.paddingSizeDefault),
          Expanded(child: Text(title, style: textMedium.copyWith(fontSize: 11.5))), // خفض الحجم قليلاً للتناسق
          
          Radio(
            value: true,
            groupValue: isSelected,
            onChanged: (v) => onTap(),
            activeColor: Theme.of(context).primaryColor,
          ),
        ]),
      ),
    );
  }
}
