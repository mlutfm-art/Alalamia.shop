import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:flutter_sixvalley_ecommerce/features/smartads/controllers/ad_controller.dart';
import 'package:flutter_sixvalley_ecommerce/features/smartads/domain/models/smart_ad_model.dart';
import 'package:provider/provider.dart';
import 'package:url_launcher/url_launcher.dart';

class InAppBannerDialog extends StatefulWidget {
  final SmartAdModel ad;
  const InAppBannerDialog({super.key, required this.ad});

  @override
  State<InAppBannerDialog> createState() => _InAppBannerDialogState();
}

class _InAppBannerDialogState extends State<InAppBannerDialog> {
  @override
  void initState() {
    super.initState();
    // Track impression when dialog opens
    if (widget.ad.id != null) {
      WidgetsBinding.instance.addPostFrameCallback((_) {
        Provider.of<AdController>(context, listen: false).trackImpression(widget.ad.id!);
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);

    return Dialog(
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      elevation: 10,
      backgroundColor: Colors.transparent,
      child: Stack(
        clipBehavior: Clip.none,
        children: [
          Container(
            width: double.infinity,
            decoration: BoxDecoration(
              color: theme.cardColor,
              borderRadius: BorderRadius.circular(16),
            ),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                // Ad Banner Image
                ClipRRect(
                  borderRadius: const BorderRadius.vertical(top: Radius.circular(16)),
                  child: widget.ad.image != null && widget.ad.image!.isNotEmpty
                      ? CachedNetworkImage(
                          imageUrl: widget.ad.image!,
                          fit: BoxFit.cover,
                          height: 180,
                          width: double.infinity,
                          placeholder: (context, url) => Container(
                            height: 180,
                            color: Colors.grey[200],
                            child: const Center(child: CircularProgressIndicator()),
                          ),
                          errorWidget: (context, url, error) => _buildPlaceholderContent(theme),
                        )
                      : _buildPlaceholderContent(theme),
                ),
                Padding(
                  padding: const EdgeInsets.all(20),
                  child: Column(
                    children: [
                      if (widget.ad.title != null)
                        Text(
                          widget.ad.title!,
                          style: TextStyle(
                            fontWeight: FontWeight.bold,
                            fontSize: 18,
                            color: theme.textTheme.bodyLarge?.color,
                          ),
                          textAlign: TextAlign.center,
                        ),
                      const SizedBox(height: 8),
                      if (widget.ad.description != null)
                        Text(
                          widget.ad.description!,
                          style: TextStyle(
                            fontSize: 13,
                            color: theme.textTheme.bodyMedium?.color?.withOpacity(0.7),
                          ),
                          textAlign: TextAlign.center,
                          maxLines: 4,
                          overflow: TextOverflow.ellipsis,
                        ),
                      const SizedBox(height: 20),
                      ElevatedButton(
                        style: ElevatedButton.styleFrom(
                          backgroundColor: theme.primaryColor,
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(24),
                          ),
                          padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
                        ),
                        onPressed: () async {
                          Navigator.pop(context);
                          if (widget.ad.id != null) {
                            Provider.of<AdController>(context, listen: false).trackClick(widget.ad.id!);
                          }
                          if (widget.ad.url != null && widget.ad.url!.isNotEmpty) {
                            try {
                              final Uri uri = Uri.parse(widget.ad.url!);
                              if (await canLaunchUrl(uri)) {
                                await launchUrl(uri, mode: LaunchMode.externalApplication);
                              }
                            } catch (e) {
                              debugPrint("Could not launch ad URL: $e");
                            }
                          }
                        },
                        child: const Text(
                          "عرض الآن",
                          style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
          Positioned(
            right: -10,
            top: -10,
            child: GestureDetector(
              onTap: () => Navigator.pop(context),
              child: Container(
                decoration: const BoxDecoration(
                  color: Colors.white,
                  shape: BoxShape.circle,
                  boxShadow: [BoxShadow(color: Colors.black26, blurRadius: 4)],
                ),
                child: const Icon(
                  Icons.close,
                  color: Colors.black54,
                  size: 28,
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildPlaceholderContent(ThemeData theme) {
    return Container(
      height: 180,
      width: double.infinity,
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [theme.primaryColor, theme.primaryColor.withOpacity(0.7)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
      ),
      child: const Center(
        child: Icon(
          Icons.campaign_outlined,
          color: Colors.white,
          size: 64,
        ),
      ),
    );
  }
}
