import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:flutter_sixvalley_ecommerce/features/smartads/domain/models/smart_ad_notification_model.dart';
import 'package:flutter_sixvalley_ecommerce/features/smartads/controllers/ad_controller.dart';

class SmartadsNotificationScreen extends StatelessWidget {
  const SmartadsNotificationScreen({Key? key}): super(key:key);
  @override
  Widget build(BuildContext context) {
    return Consumer<AdController>(builder: (context, ctrl, _) {
      return Scaffold(appBar: AppBar(title: Text('Notifications')), body: ListView(children: [Text('Notifications - action_engine handling')],),);
    });
  }
}
