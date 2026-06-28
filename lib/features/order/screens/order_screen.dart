import 'package:flutter/material.dart';
import 'package:flutter_sixvalley_ecommerce/features/order/controllers/order_controller.dart';
import 'package:flutter_sixvalley_ecommerce/features/order/widgets/order_shimmer_widget.dart';
import 'package:flutter_sixvalley_ecommerce/features/order/widgets/order_type_button_widget.dart';
import 'package:flutter_sixvalley_ecommerce/features/order/widgets/order_widget.dart';
import 'package:flutter_sixvalley_ecommerce/helper/route_healper.dart';
import 'package:flutter_sixvalley_ecommerce/localization/language_constrants.dart';
import 'package:flutter_sixvalley_ecommerce/main.dart';
import 'package:flutter_sixvalley_ecommerce/features/auth/controllers/auth_controller.dart';
import 'package:flutter_sixvalley_ecommerce/utill/dimensions.dart';
import 'package:flutter_sixvalley_ecommerce/utill/images.dart';
import 'package:flutter_sixvalley_ecommerce/common/basewidget/custom_app_bar_widget.dart';
import 'package:flutter_sixvalley_ecommerce/common/basewidget/no_internet_screen_widget.dart';
import 'package:flutter_sixvalley_ecommerce/common/basewidget/not_loggedin_widget.dart';
import 'package:flutter_sixvalley_ecommerce/common/basewidget/paginated_list_view_widget.dart';
import 'package:provider/provider.dart';
import 'package:url_launcher/url_launcher.dart';
import 'package:flutter_sixvalley_ecommerce/features/splash/controllers/splash_controller.dart';

class OrderScreen extends StatefulWidget {
  final bool isBacButtonExist;
  final bool fromDashboard;
  final bool fromPlaceOrder;
  const OrderScreen({super.key, this.isBacButtonExist = true, this.fromDashboard = false, this.fromPlaceOrder = false}) ;

  @override
  State<OrderScreen> createState() => _OrderScreenState();
}

class _OrderScreenState extends State<OrderScreen> {
  ScrollController scrollController  = ScrollController();
   bool isGuestMode = !Provider.of<AuthController>(Get.context!, listen: false).isLoggedIn();
  @override
  void initState() {
    if(!isGuestMode){
      Provider.of<OrderController>(context, listen: false).setIndex(0, notify: false);
      Provider.of<OrderController>(context, listen: false).getOrderList(1,'ongoing');
    }
    super.initState();
  }

  @override
  Widget build(BuildContext context) {
    return PopScope(
      canPop: Navigator.canPop(context),
      onPopInvokedWithResult: (didPop, result) async {
        if(widget.fromPlaceOrder) {
          RouterHelper.getDashboardRoute(action: RouteAction.pushReplacement, page: 'home');
        } else {
          return;
        }
      },
      child: Scaffold(
       appBar: CustomAppBar(title: getTranslated('order', context), isBackButtonExist: widget.isBacButtonExist,
       onBackPressed: () {
         if(widget.fromPlaceOrder) {
           RouterHelper.getDashboardRoute(action: RouteAction.pushReplacement, page: 'home');
         } else {
           Navigator.of(context).pop();
         }
        },
       ),
        body: isGuestMode ? NotLoggedInWidget(message: getTranslated('to_view_the_order_history', context), fromPage: widget.fromDashboard ? '${RouterHelper.dashboardScreen}?page=orders' : RouterHelper.orderScreen, ) :
        Consumer<OrderController>(
          builder: (context, orderController, child) {
            return Column(children: [


              Padding(padding: const EdgeInsets.all(Dimensions.paddingSizeLarge),
                child: Row(children: [
                  OrderTypeButton(text: getTranslated('RUNNING', context), index: 0),
                  const SizedBox(width: Dimensions.paddingSizeSmall),
                  OrderTypeButton(text: getTranslated('DELIVERED', context), index: 1),
                  const SizedBox(width: Dimensions.paddingSizeSmall),
                  OrderTypeButton(text: getTranslated('CANCELED', context), index: 2)])),



                Expanded(child: orderController.orderModel != null ? (orderController.orderModel!.orders != null &&
                    orderController.orderModel!.orders!.isNotEmpty)?
                  SingleChildScrollView(
                    controller: scrollController,
                    child: PaginatedListView(scrollController: scrollController,
                      onPaginate: (int? offset) async{
                        await orderController.getOrderList(offset!, orderController.selectedType);
                      },
                      totalSize: orderController.orderModel?.totalSize,
                      offset: orderController.orderModel?.offset != null ? int.parse(orderController.orderModel!.offset!):1,
                      itemView: ListView.builder(
                        shrinkWrap: true,
                        physics: const NeverScrollableScrollPhysics(),
                        itemCount: orderController.orderModel?.orders!.length,
                        padding: const EdgeInsets.all(0),
                        itemBuilder: (context, index) => OrderWidget(orderModel: orderController.orderModel?.orders![index]),
                      ),
                    ),
                  ) : const NoInternetOrDataScreenWidget(isNoInternet: false, icon: Images.noOrder, message: 'no_order_found') :
                    const OrderShimmerWidget()
                )

              ],
            );
          }
        ),
        floatingActionButton: FloatingActionButton(
          onPressed: () async {
            String phone = Provider.of<SplashController>(context, listen: false).configModel!.companyPhone ?? "";
            var whatsappUrl = "whatsapp://send?phone=$phone";
            if (await canLaunchUrl(Uri.parse(whatsappUrl))) {
              await launchUrl(Uri.parse(whatsappUrl));
            } else {
              ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text("WhatsApp not installed")));
            }
          },
          backgroundColor: Colors.green,
          child: Image.asset(Images.callIcon, color: Colors.white, width: 25, height: 25), // يمكنك استبدالها بأيقونة الواتساب إذا كانت متوفرة في Images
        ),
      ),
    );
  }
}
