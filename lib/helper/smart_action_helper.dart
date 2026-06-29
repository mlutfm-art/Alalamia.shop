import 'package:flutter/material.dart';
import 'package:flutter_sixvalley_ecommerce/helper/route_healper.dart'; 
import 'package:flutter_sixvalley_ecommerce/common/basewidget/show_custom_snakbar_widget.dart'; 
import 'package:url_launcher/url_launcher.dart';
import 'package:provider/provider.dart';
import 'package:flutter_sixvalley_ecommerce/features/profile/controllers/profile_contrroller.dart';

class SmartActionHelper {
  static Future<void> performAction(BuildContext context, Map<String, dynamic>? action) async {
    if (action == null || action['type'] == 'none') return;

    final String type = action['type'] ?? action['action_type'] ?? '';
    final Map<String, dynamic> payload = action['payload'] ?? {};
    final String? deepLink = action['deep_link'];
    final String? fallbackUrl = action['fallback_url'];

    try {
      switch (type) {
        case 'product':
          int? id = int.tryParse(payload['product_id']?.toString() ?? payload['id']?.toString() ?? '');
          if (id != null) RouterHelper.getProductDetailsRoute(productId: id);
          break;

        case 'category':
          int? id = int.tryParse(payload['category_id']?.toString() ?? payload['id']?.toString() ?? '');
          if (id != null) RouterHelper.getBrandCategoryRoute(id: id, isBrand: false);
          break;

        case 'wallet':
          RouterHelper.getWalletRoute();
          break;

        case 'order_tracking':
          if (payload['order_id'] != null) {
            RouterHelper.getOrderDetailsScreenRoute(orderId: int.parse(payload['order_id'].toString()));
          }
          break;

        default:
          if (deepLink != null || fallbackUrl != null) {
             final Uri uri = Uri.parse(deepLink ?? fallbackUrl!);
             if (await canLaunchUrl(uri)) {
                await launchUrl(uri, mode: LaunchMode.externalApplication);
             }
          }
      }
    } catch (e) {
      debugPrint("Action Engine Error: $e");
    }
  }

  static String processDynamicText(BuildContext context, String text) {
    try {
      final profile = Provider.of<ProfileController>(context, listen: false).userInfoModel;
      if (profile == null) return text;
      return text.replaceAll('{{user_name}}', '${profile.fName} ${profile.lName}');
    } catch (e) {
      return text;
    }
  }
}
