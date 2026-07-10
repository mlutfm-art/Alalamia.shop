import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:flutter_sixvalley_ecommerce/features/smartads/controllers/ad_controller.dart';
import 'package:flutter_sixvalley_ecommerce/helper/route_healper.dart';

class SmartAdFeedScreen extends StatelessWidget {
  const SmartAdFeedScreen({Key? key}): super(key:key);
  @override
  Widget build(BuildContext context) {
    return Consumer<AdController>(builder: (context, controller, _) {
      return Scaffold(
        appBar: AppBar(title: Text('Smart Ads')),
        body: Center(child: controller.isLoading ? CircularProgressIndicator() : Text('Ads: ${controller.dataList.length}')),
        floatingActionButton: FloatingActionButton(onPressed: () => Navigator.pushNamed(context, RouterHelper.getSmartAdsNotificationRoute()), child: Icon(Icons.add)),
      );
    });
  }
}
