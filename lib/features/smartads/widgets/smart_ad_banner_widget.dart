import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter_sixvalley_ecommerce/features/smartads/domain/models/smart_ad_model.dart';

class SmartAdBannerWidget extends StatelessWidget {
  final SmartAdModel ad;
  final VoidCallback? onTap;
  const SmartAdBannerWidget({required this.ad, this.onTap, Key? key}): super(key: key);

  @override
  Widget build(BuildContext context) {
    final image = ad.imageUrl ?? ad.image ?? ad.photo ?? ad.picture ?? ad.img;
    return GestureDetector(
      onTap: onTap,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          if (image != null) CachedNetworkImage(imageUrl: image, width: double.infinity, height: 160, fit: BoxFit.cover),
          if (ad.subTitle != null) Padding(padding: EdgeInsets.all(8), child: Text(ad.subTitle!)),
        ],
      ),
    );
  }
}
