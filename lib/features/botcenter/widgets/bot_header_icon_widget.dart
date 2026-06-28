import 'package:flutter/material.dart';
import 'package:flutter_sixvalley_ecommerce/helper/route_healper.dart';

class BotHeaderIconWidget extends StatefulWidget {
  const BotHeaderIconWidget({super.key});

  @override
  State<BotHeaderIconWidget> createState() => _BotHeaderIconWidgetState();
}

class _BotHeaderIconWidgetState extends State<BotHeaderIconWidget>
    with SingleTickerProviderStateMixin {
  late AnimationController _pulseController;
  late Animation<double> _scaleAnimation;

  @override
  void initState() {
    super.initState();
    _pulseController = AnimationController(
      vsync: this,
      duration: const Duration(seconds: 2),
    )..repeat(reverse: true);
    _scaleAnimation = Tween<double>(begin: 1.0, end: 1.12).animate(
      CurvedAnimation(parent: _pulseController, curve: Curves.easeInOut),
    );
  }

  @override
  void dispose() {
    _pulseController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return AnimatedBuilder(
      animation: _scaleAnimation,
      builder: (context, child) {
        return Transform.scale(
          scale: _scaleAnimation.value,
          child: InkWell(
            onTap: () => RouterHelper.getBotChatRoute(action: RouteAction.push),
            borderRadius: BorderRadius.circular(12),
            child: Container(
              padding: const EdgeInsets.all(6),
              decoration: BoxDecoration(
                gradient: const LinearGradient(
                  colors: [Color(0xFF0D9488), Color(0xFF10B981)],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
                borderRadius: BorderRadius.circular(10),
                boxShadow: [
                  BoxShadow(
                    color: const Color(0xFF10B981).withOpacity(0.3),
                    blurRadius: 8,
                    offset: const Offset(0, 2),
                  ),
                ],
              ),
              child: const Text("🤖", style: TextStyle(fontSize: 20)),
            ),
          ),
        );
      },
    );
  }
}
