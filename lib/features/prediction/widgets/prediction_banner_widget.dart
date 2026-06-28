import 'package:flutter/material.dart';
import 'package:flutter_sixvalley_ecommerce/features/prediction/controllers/prediction_controller.dart';
import 'package:flutter_sixvalley_ecommerce/features/prediction/domain/models/prediction_model.dart';
import 'package:flutter_sixvalley_ecommerce/helper/route_healper.dart';
import 'package:flutter_sixvalley_ecommerce/theme/controllers/theme_controller.dart';
import 'package:flutter_sixvalley_ecommerce/utill/custom_themes.dart';
import 'package:provider/provider.dart';

class PredictionBannerWidget extends StatefulWidget {
  const PredictionBannerWidget({super.key});

  @override
  State<PredictionBannerWidget> createState() => _PredictionBannerWidgetState();
}

class _PredictionBannerWidgetState extends State<PredictionBannerWidget> with SingleTickerProviderStateMixin {
  late AnimationController _pulseController;
  late Animation<double> _glowAnimation;

  @override
  void initState() {
    super.initState();
    _pulseController = AnimationController(
      vsync: this, duration: const Duration(seconds: 2))..repeat(reverse: true);
    _glowAnimation = Tween<double>(begin: 4.0, end: 12.0).animate(CurvedAnimation(parent: _pulseController, curve: Curves.easeInOut));
  }

  @override
  void dispose() {
    _pulseController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Consumer2<PredictionController, ThemeController>(
      builder: (context, controller, themeController, child) {
        PredictionBannerModel? banner = controller.predictionBanner;
        
        if (banner == null || banner.showBanner != true) {
          return const SizedBox.shrink();
        }

        final String title = banner.title ?? '';
        final String description = banner.description ?? '';
        final String buttonText = banner.buttonText ?? 'توقع الآن';
        final String matchTitle = banner.matchTitle ?? '';

        final bool isDark = themeController.darkTheme;
        final Color glowColor = isDark ? const Color(0xFFC084FC) : const Color(0xFF34D399);
        final List<Color> gradientColors = isDark 
            ? [const Color(0xFF1E1B4B), const Color(0xFF0F0E26)]
            : [const Color(0xFF0D9488), const Color(0xFF10B981)];

        return AnimatedBuilder(
          animation: _glowAnimation,
          builder: (context, _) {
            return Container(
              margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(16),
                gradient: LinearGradient(colors: gradientColors),
                boxShadow: [BoxShadow(color: glowColor.withOpacity(0.2), blurRadius: _glowAnimation.value)],
              ),
              child: Material(
                color: Colors.transparent,
                child: InkWell(
                  onTap: () => RouterHelper.getPredictionHubRoute(action: RouteAction.push),
                  borderRadius: BorderRadius.circular(16),
                  child: Padding(
                    padding: const EdgeInsets.all(18),
                    child: Row(
                      children: [
                        const Text("⚽", style: TextStyle(fontSize: 35)),
                        const SizedBox(width: 15),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Text(matchTitle, style: textMedium.copyWith(color: Colors.white, fontSize: 10)),
                              Text(title, style: textBold.copyWith(color: Colors.white, fontSize: 16)),
                              Text(description, style: textRegular.copyWith(color: Colors.white70, fontSize: 11), maxLines: 1),
                            ],
                          ),
                        ),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                          decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(20)),
                          child: Text(buttonText, style: textBold.copyWith(color: gradientColors[0], fontSize: 12)),
                        ),
                      ],
                    ),
                  ),
                ),
              ),
            );
          },
        );
      },
    );
  }
}
