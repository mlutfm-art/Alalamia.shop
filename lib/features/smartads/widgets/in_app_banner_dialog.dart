import 'package:flutter/material.dart';
import 'package:flutter_sixvalley_ecommerce/features/smartads/domain/models/smart_ad_model.dart';

class InAppBannerDialog extends StatelessWidget {
  final SmartAdModel ad;
  const InAppBannerDialog({required this.ad, Key? key}): super(key: key);

  @override
  Widget build(BuildContext context) {
    return Dialog(
      child: Column(mainAxisSize: MainAxisSize.min, children: [
        if (ad.imageUrl != null) Image.network(ad.imageUrl!),
        if (ad.title != null) Padding(padding:EdgeInsets.all(8), child: Text(ad.title!, style: TextStyle(fontSize:18, fontWeight: FontWeight.bold))),
        if (ad.buttonText != null) TextButton(onPressed: ()=> Navigator.pop(context), child: Text(ad.buttonText!)),
      ]),
    );
  }
}
