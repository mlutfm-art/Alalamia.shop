import 'package:flutter/material.dart';
import 'package:flutter_sixvalley_ecommerce/common/basewidget/custom_app_bar_widget.dart';
import 'package:flutter_sixvalley_ecommerce/localization/language_constrants.dart';
import 'package:flutter_sixvalley_ecommerce/features/deal/controllers/flash_deal_controller.dart';
import 'package:flutter_sixvalley_ecommerce/utill/dimensions.dart';
import 'package:flutter_sixvalley_ecommerce/common/basewidget/title_row_widget.dart';
import 'package:flutter_sixvalley_ecommerce/features/deal/widgets/flash_deals_list_widget.dart';
import 'package:provider/provider.dart';

class FlashDealScreenView extends StatefulWidget {
  const FlashDealScreenView({super.key});
  @override
  State<FlashDealScreenView> createState() => _FlashDealScreenViewState();
}
class _FlashDealScreenViewState extends State<FlashDealScreenView> {
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: CustomAppBar(title: getTranslated('flash_deal', context)),
      body: Column(children: [
        SafeArea(
          child: Padding(padding: const EdgeInsets.only(
            left:  Dimensions.paddingSizeSmall,
            right:  Dimensions.paddingSizeSmall,
            top:  Dimensions.paddingSizeSmall,
          ),
            child: Consumer<FlashDealController>(
              builder: (context, flashController, _) {
                return ValueListenableBuilder<Duration?>(
                  valueListenable: flashController.durationNotifier,
                  builder: (context, duration, _) {
                    return TitleRowWidget(
                      title: getTranslated('flash_deal', context)?.toUpperCase(),
                      eventDuration: duration, 
                      isFlash: true, 
                      isBackExist: true
                    );
                  }
                );
              }
            )),
        ),


        Expanded(child: RefreshIndicator(
          onRefresh: () async => await Provider.of<FlashDealController>(context, listen: false).getFlashDealList(true, false),
          child: const Padding(padding: EdgeInsets.only(
            top: Dimensions.paddingSizeExtraSmall,
            left:  Dimensions.paddingSizeSmall,
            right:  Dimensions.paddingSizeSmall,
            bottom:  Dimensions.paddingSizeSmall,
          ),
          child: FlashDealsListWidget(isHomeScreen: false))))]));
  }
}
