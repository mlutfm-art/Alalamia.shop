import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:flutter_sixvalley_ecommerce/features/smartads/controllers/ad_controller.dart';
import 'package:flutter_sixvalley_ecommerce/features/smartads/domain/models/smart_ad_model.dart';
import 'package:provider/provider.dart';
import 'package:url_launcher/url_launcher.dart';

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
        // Find first active ad matching the requested placement
        final List<SmartAdModel> ads = adController.activeAds
            .where((ad) => ad.placement == widget.placement)
            .toList();

        if (ads.isEmpty) {
          return const SizedBox.shrink();
        }

        final ad = ads.first;

        // Trigger impression once when the widget is rendered
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
            boxShadow: [
              BoxShadow(
                color: Colors.black.withOpacity(0.06),
                blurRadius: 10,
                offset: const Offset(0, 4),
              )
            ],
          ),
          child: ClipRRect(
            borderRadius: BorderRadius.circular(12),
            child: InkWell(
              onTap: () async {
                if (ad.id != null) {
                  adController.trackClick(ad.id!);
                }
                if (ad.url != null && ad.url!.isNotEmpty) {
                  try {
                    final Uri uri = Uri.parse(ad.url!);
                    if (await canLaunchUrl(uri)) {
                      await launchUrl(uri, mode: LaunchMode.externalApplication);
                    }
                  } catch (e) {
                    debugPrint("Could not launch ad URL: $e");
                  }
                }
              },
              child: Stack(
                children: [
                  ad.image != null && ad.image!.isNotEmpty
                      ? CachedNetworkImage(
                          imageUrl: ad.image!,
                          fit: BoxFit.cover,
                          width: double.infinity,
                          height: 120,
                          placeholder: (context, url) => Container(
                            height: 120,
                            color: Colors.grey[200],
                            child: const Center(
                              child: CircularProgressIndicator(),
                            ),
                          ),
                          errorWidget: (context, url, error) => _buildPlaceholderAd(ad),
                        )
                      : _buildPlaceholderAd(ad),
                  Positioned(
                    top: 8,
                    right: 8,
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                      decoration: BoxDecoration(
                        color: Colors.black.withOpacity(0.5),
                        borderRadius: BorderRadius.circular(4),
                      ),
                      child: const Text(
                        "إعلان",
                        style: TextStyle(color: Colors.white, fontSize: 10),
                      ),
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

  Widget _buildPlaceholderAd(SmartAdModel ad) {
    final theme = Theme.of(context);
    return Container(
      width: double.infinity,
      height: 120,
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [
            theme.primaryColor.withOpacity(0.85),
            theme.primaryColor.withOpacity(0.6),
          ],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
      ),
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          if (ad.title != null)
            Text(
              ad.title!,
              style: const TextStyle(
                color: Colors.white,
                fontWeight: FontWeight.bold,
                fontSize: 16,
              ),
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
            ),
          const SizedBox(height: 6),
          if (ad.description != null)
            Text(
              ad.description!,
              style: TextStyle(
                color: Colors.white.withOpacity(0.9),
                fontSize: 12,
              ),
              maxLines: 2,
              overflow: TextOverflow.ellipsis,
            ),
        ],
      ),
    );
  }
}
