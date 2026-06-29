import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:flutter_sixvalley_ecommerce/features/smartads/controllers/ad_controller.dart';
import 'package:flutter_sixvalley_ecommerce/features/smartads/domain/models/smart_ad_model.dart';
import 'package:flutter_sixvalley_ecommerce/helper/smart_action_helper.dart'; // استيراد المحرك الجديد
import 'package:provider/provider.dart';

class SmartAdBannerWidget extends StatefulWidget {
  final String placement;
  const SmartAdBannerWidget({super.key, required this.placement});

  @override
  State<SmartAdBannerWidget> createState() => _SmartAdBannerWidgetState();
}

class _SmartAdBannerWidgetState extends State<SmartAdBannerWidget> {
  bool _impressionTracked = false;

  @override
  Widget build(BuildContext context) {
    return Consumer<AdController>(
      builder: (context, adController, child) {
        final List<SmartAdModel> ads = adController.activeAds.where((ad) {
          return ad.placement == widget.placement || 
                 (widget.placement.startsWith('home') && ad.placement == 'home');
        }).toList();

        if (ads.isEmpty) return const SizedBox.shrink();

        final ad = ads.first;

        // تتبع الظهور تلقائياً
        if (!_impressionTracked && ad.id != null) {
          _impressionTracked = true;
          WidgetsBinding.instance.addPostFrameCallback((_) {
            adController.trackImpression(ad.id!);
          });
        }

        return Container(
          margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(12),
            color: ad.backgroundColor != null 
                ? Color(int.parse(ad.backgroundColor!.replaceFirst('#', '0xFF'))) 
                : Colors.transparent,
          ),
          child: ClipRRect(
            borderRadius: BorderRadius.circular(12),
            child: InkWell(
              onTap: () {
                // تتبع النقرة
                if (ad.id != null) adController.trackClick(ad.id!);
                
                // تشغيل محرك التفاعل الذكي (Smart Engagement Engine)
                SmartActionHelper.performAction(context, ad.actionPayload);
              },
              child: Stack(
                children: [
                  if (ad.image != null && ad.image!.isNotEmpty)
                    CachedNetworkImage(
                      imageUrl: ad.image!,
                      fit: BoxFit.cover,
                      width: double.infinity,
                      height: 120,
                      placeholder: (context, url) => Container(height: 120, color: Colors.grey[100]),
                      errorWidget: (context, url, error) => _buildPlaceholder(ad),
                    )
                  else
                    _buildPlaceholder(ad),
                  
                  // وسم "إعلان" صغير
                  Positioned(
                    top: 8, right: 8,
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                      decoration: BoxDecoration(
                        color: Colors.black.withOpacity(0.4), 
                        borderRadius: BorderRadius.circular(4)
                      ),
                      child: const Text("إعلان", style: TextStyle(color: Colors.white, fontSize: 8)),
                    ),
                  ),
                ],
              ),
            ),
          ),
        );
      },
    );
  }

  Widget _buildPlaceholder(SmartAdModel ad) {
    return Container(
      width: double.infinity, height: 100,
      padding: const EdgeInsets.all(16),
      alignment: Alignment.center,
      child: Text(
        ad.title ?? '', 
        textAlign: TextAlign.center, 
        style: TextStyle(
          fontWeight: FontWeight.bold, 
          color: ad.textColor != null 
              ? Color(int.parse(ad.textColor!.replaceFirst('#', '0xFF'))) 
              : Colors.black
        )
      ),
    );
  }
}
