import 'package:flutter/material.dart';
import 'package:flutter_sixvalley_ecommerce/helper/smart_action_helper.dart';
import 'package:url_launcher/url_launcher.dart';
import 'package:share_plus/share_plus.dart';

class SmartActionHelper {
  static Future<void> handleAction(Map<String,dynamic>? action, BuildContext context) async {
    if (action == null) return;
    final type = action['type']?.toString() ?? action['action_type']?.toString();
    final payload = action['payload'] ?? {};
    switch(type) {
      case 'product':
        // navigate to product
        break;
      case 'category':
        break;
      case 'url':
        final url = payload['url']?.toString() ?? action['deep_link']?.toString();
        if (url != null && await canLaunchUrl(Uri.parse(url))) { await launchUrl(Uri.parse(url)); }
        break;
      case 'share_content':
      case 'share':
        Share.share(payload['text']?.toString() ?? '');
        break;
      case 'open_whatsapp':
        final phone = payload['phone']?.toString();
        if (phone != null) launchUrl(Uri.parse('https://wa.me/$phone'));
        break;
      // add many other cases as needed (placeholder)
      default:
        // default deep link handling
        final deep = action['deep_link']?.toString();
        if (deep != null) {
          if (await canLaunchUrl(Uri.parse(deep))) await launchUrl(Uri.parse(deep));
        }
    }
  }
}
