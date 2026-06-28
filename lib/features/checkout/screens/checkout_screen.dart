import 'package:flutter/material.dart';
import 'package:flutter_sixvalley_ecommerce/features/address/controllers/address_controller.dart';
import 'package:flutter_sixvalley_ecommerce/features/cart/domain/models/cart_model.dart';
import 'package:flutter_sixvalley_ecommerce/features/checkout/controllers/checkout_controller.dart';
import 'package:flutter_sixvalley_ecommerce/features/checkout/widgets/checkout_condition_checkbox.dart';
import 'package:flutter_sixvalley_ecommerce/features/checkout/widgets/order_place_bottomsheet_widget.dart';
import 'package:flutter_sixvalley_ecommerce/features/checkout/widgets/payment_method_bottom_sheet_widget.dart';
import 'package:flutter_sixvalley_ecommerce/features/profile/controllers/profile_contrroller.dart';
import 'package:flutter_sixvalley_ecommerce/features/shipping/controllers/shipping_controller.dart';
import 'package:flutter_sixvalley_ecommerce/helper/price_converter.dart';
import 'package:flutter_sixvalley_ecommerce/helper/route_healper.dart';
import 'package:flutter_sixvalley_ecommerce/localization/language_constrants.dart';
import 'package:flutter_sixvalley_ecommerce/main.dart';
import 'package:flutter_sixvalley_ecommerce/features/auth/controllers/auth_controller.dart';
import 'package:flutter_sixvalley_ecommerce/features/cart/controllers/cart_controller.dart';
import 'package:flutter_sixvalley_ecommerce/features/coupon/controllers/coupon_controller.dart';
import 'package:flutter_sixvalley_ecommerce/features/splash/controllers/splash_controller.dart';
import 'package:flutter_sixvalley_ecommerce/utill/custom_themes.dart';
import 'package:flutter_sixvalley_ecommerce/utill/dimensions.dart';
import 'package:flutter_sixvalley_ecommerce/common/basewidget/custom_app_bar_widget.dart';
import 'package:flutter_sixvalley_ecommerce/common/basewidget/custom_button_widget.dart';
import 'package:flutter_sixvalley_ecommerce/common/basewidget/show_custom_snakbar_widget.dart';
import 'package:flutter_sixvalley_ecommerce/common/basewidget/custom_textfield_widget.dart';
import 'package:flutter_sixvalley_ecommerce/features/checkout/widgets/choose_payment_widget.dart';
import 'package:flutter_sixvalley_ecommerce/features/checkout/widgets/coupon_apply_widget.dart';
import 'package:flutter_sixvalley_ecommerce/features/checkout/widgets/shipping_details_widget.dart';
import 'package:provider/provider.dart';

class CheckoutScreen extends StatefulWidget {
  final List<CartModel> cartList;
  final bool fromProductDetails;
  final double totalOrderAmount;
  final double shippingFee;
  final double discount;
  final double tax;
  final int? sellerId;
  final bool onlyDigital;
  final bool hasPhysical;
  final int quantity;

  const CheckoutScreen({super.key, required this.cartList, this.fromProductDetails = false,
    required this.discount, required this.tax, required this.totalOrderAmount, required this.shippingFee,
    this.sellerId, this.onlyDigital = false, required this.quantity, required this.hasPhysical});

  @override
  CheckoutScreenState createState() => CheckoutScreenState();
}

class CheckoutScreenState extends State<CheckoutScreen> {
  final GlobalKey<ScaffoldMessengerState> _scaffoldKey = GlobalKey<ScaffoldMessengerState>();
  final TextEditingController _couponController = TextEditingController();
  final GlobalKey<FormState> passwordFormKey = GlobalKey<FormState>();
  final FocusNode _orderNoteNode = FocusNode();
  
  double _order = 0;
  double _tax = 0;
  late bool _billingAddress;

  @override
  void initState() {
    super.initState();
    _initCheckout();
  }

  void _initCheckout() {
    final authProvider = Provider.of<AuthController>(context, listen: false);
    Provider.of<AddressController>(context, listen: false).getAddressList();
    Provider.of<CheckoutController>(context, listen: false).getReferralAmount('0');
    Provider.of<CouponController>(context, listen: false).removePrevCouponData();
    Provider.of<CartController>(context, listen: false).getCartData(context);
    Provider.of<CheckoutController>(context, listen: false).resetPaymentMethod();
    Provider.of<ShippingController>(context, listen: false).getChosenShippingMethod(context);
    
    if(Provider.of<SplashController>(context, listen: false).configModel?.offlinePayment != null) {
      Provider.of<CheckoutController>(context, listen: false).getOfflinePaymentList();
    }
    if(authProvider.isLoggedIn()){
      Provider.of<CouponController>(context, listen: false).getAvailableCouponList();
    }
    _billingAddress = Provider.of<SplashController>(context, listen: false).configModel!.billingInputByCustomer == 1;
    Provider.of<CheckoutController>(context, listen: false).clearData();
  }

  @override
  Widget build(BuildContext context) {
    _order = widget.totalOrderAmount + widget.discount;
    _tax = widget.tax;

    return Scaffold(
      key: _scaffoldKey,
      appBar: CustomAppBar(title: getTranslated('checkout', context)),
      body: Column(children: [
        Expanded(
          child: ListView(
            physics: const BouncingScrollPhysics(),
            children: [
              ShippingDetailsWidget(
                hasPhysical: widget.hasPhysical,
                billingAddress: _billingAddress,
                passwordFormKey: passwordFormKey,
              ),

              if (Provider.of<AuthController>(context, listen: false).isLoggedIn())
                _sectionWrapper(
                  child: CouponApplyWidget(couponController: _couponController, orderAmount: _order),
                ),

              _sectionWrapper(child: ChoosePaymentWidget(onlyDigital: widget.onlyDigital)),

              const SizedBox(height: Dimensions.paddingSizeSmall),

              Padding(
                padding: const EdgeInsets.symmetric(horizontal: Dimensions.paddingSizeDefault),
                child: Consumer<CheckoutController>(
                  builder: (context, checkoutController, child) {
                    double couponDiscount = Provider.of<CouponController>(context).discount ?? 0;
                    double totalDiscount = widget.discount + couponDiscount;
                    double totalPayable = (_order + widget.shippingFee - totalDiscount + _tax);

                    return Container(
                      padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
                      decoration: BoxDecoration(
                        color: Theme.of(context).cardColor,
                        borderRadius: BorderRadius.circular(15),
                        boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.03), blurRadius: 10)],
                      ),
                      child: Column(children: [
                        _summaryRow('${getTranslated('sub_total', context)} (${widget.quantity} قطع)', PriceConverter.convertPrice(context, _order)),
                        _summaryRow(getTranslated('shipping_fee', context)!, "(+) " + PriceConverter.convertPrice(context, widget.shippingFee)),
                        if (totalDiscount > 0)
                          _summaryRow(getTranslated('total_discount', context) ?? "إجمالي التوفير", "(-) " + PriceConverter.convertPrice(context, totalDiscount), color: Colors.green),
                        const Padding(padding: EdgeInsets.symmetric(vertical: 6), child: Divider(height: 1)),
                        Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
                          Text(getTranslated('total_payable', context)!, style: textBold.copyWith(fontSize: 16)),
                          Text(PriceConverter.convertPrice(context, totalPayable), 
                            style: textBold.copyWith(fontSize: 20, color: Theme.of(context).primaryColor)),
                        ]),
                      ]),
                    );
                  },
                ),
              ),

              Padding(
                padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
                child: CustomTextFieldWidget(
                  hintText: getTranslated('order_note', context),
                  inputType: TextInputType.multiline,
                  inputAction: TextInputAction.done,
                  maxLines: 2,
                  focusNode: _orderNoteNode,
                  controller: Provider.of<CheckoutController>(context).orderNoteController,
                ),
              ),
              const SizedBox(height: 10),
            ],
          ),
        ),

        _buildBottomButton(),
      ]),
    );
  }

  Widget _summaryRow(String title, String value, {Color? color}) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 2),
      child: Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
        Text(title, style: textRegular.copyWith(fontSize: 12, color: color ?? Theme.of(context).hintColor)),
        Text(value, style: textMedium.copyWith(fontSize: 12, color: color)),
      ]),
    );
  }

  Widget _sectionWrapper({required Widget child}) {
    return Container(
      margin: const EdgeInsets.fromLTRB(Dimensions.paddingSizeDefault, 0, Dimensions.paddingSizeDefault, Dimensions.paddingSizeDefault),
      padding: const EdgeInsets.all(Dimensions.paddingSizeSmall),
      decoration: BoxDecoration(
        color: Theme.of(context).cardColor,
        borderRadius: BorderRadius.circular(15),
        boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.02), blurRadius: 5)],
      ),
      child: child,
    );
  }

  Widget _buildBottomButton() {
    return Consumer<CheckoutController>(
      builder: (context, orderProvider, child) {
        return Container(
          padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
          decoration: BoxDecoration(
            color: Theme.of(context).cardColor,
            boxShadow: [BoxShadow(color: Theme.of(context).hintColor.withOpacity(0.1), blurRadius: 10, offset: const Offset(0, -5))],
          ),
          child: Column(mainAxisSize: MainAxisSize.min, children: [
            const CheckoutConditionCheckBox(),
            const SizedBox(height: 8),
            CustomButton(
              isLoading: orderProvider.isLoading,
              buttonText: getTranslated('proceed', context),
              onTap: (!orderProvider.isAcceptTerms || orderProvider.isLoading) ? null : () => _onPlaceOrder(),
            ),
          ]),
        );
      },
    );
  }

  void _onPlaceOrder() {
    final orderProvider = Provider.of<CheckoutController>(context, listen: false);
    final locationProvider = Provider.of<AddressController>(context, listen: false);
    final couponProvider = Provider.of<CouponController>(context, listen: false);

    if(orderProvider.addressIndex == null && widget.hasPhysical) {
      showCustomSnackBarWidget(getTranslated('select_a_shipping_address', context), context, snackBarType: SnackBarType.warning);
      return;
    }

    String addressId = locationProvider.addressList![orderProvider.addressIndex!].id.toString();
    String couponCode = couponProvider.discount != null && couponProvider.discount != 0 ? couponProvider.couponCode : '';
    String couponAmount = couponProvider.discount?.toString() ?? '0';

    if(orderProvider.paymentMethodIndex != -1) {
      orderProvider.digitalPaymentPlaceOrder(
        orderNote: orderProvider.orderNoteController.text.trim(),
        addressId: addressId, billingAddressId: addressId,
        couponCode: couponCode, couponDiscount: couponAmount,
        paymentMethod: orderProvider.selectedDigitalPaymentMethodName,
      );
    } else if (orderProvider.isCODChecked) {
      orderProvider.placeOrder(callback: _callback, addressID: addressId, couponCode: couponCode, couponAmount: couponAmount, billingAddressId: addressId, orderNote: orderProvider.orderNoteController.text.trim());
    } else {
      showModalBottomSheet(context: context, isScrollControlled: true, backgroundColor: Colors.transparent, builder: (c) => PaymentMethodBottomSheetWidget(onlyDigital: widget.onlyDigital));
    }
  }

  void _callback(bool isSuccess, String message, String orderID, bool createAccount) {
    if(isSuccess) {
      // 1. الانتقال أولاً للصفحة الرئيسية لتنظيف مكدس الصفحات
      RouterHelper.getDashboardRoute(action: RouteAction.pushReplacement, page: 'home');
      
      // 2. إظهار الفاتورة فوق الصفحة الرئيسية لضمان ثباتها ورؤيتها
      Future.delayed(const Duration(milliseconds: 600), () {
        showModalBottomSheet(
          context: Get.context!, 
          isScrollControlled: true, 
          isDismissible: false,
          enableDrag: false,
          backgroundColor: Colors.transparent, 
          builder: (context) => OrderPlaceBottomSheetWidget(
            orderID: orderID, 
            icon: Icons.check, 
            title: getTranslated('order_placed', context), 
            description: getTranslated('your_order_placed', context), 
            isFailed: false
          )
        );
      });
    } else {
      showCustomSnackBarWidget(message, context, snackBarType: SnackBarType.error);
    }
  }
}
