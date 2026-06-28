import 'package:flutter/material.dart';

class FeedbackDialog extends StatefulWidget {
  final String rating; // 'useful' or 'not_useful'
  final Function(String? feedbackText) onSubmit;

  const FeedbackDialog({
    super.key,
    required this.rating,
    required this.onSubmit,
  });

  @override
  State<FeedbackDialog> createState() => _FeedbackDialogState();
}

class _FeedbackDialogState extends State<FeedbackDialog> {
  final TextEditingController _feedbackController = TextEditingController();

  @override
  void dispose() {
    _feedbackController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final bool isPositive = widget.rating == 'useful';

    return Dialog(
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      child: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(
              isPositive ? Icons.thumb_up_alt_rounded : Icons.thumb_down_alt_rounded,
              color: isPositive ? const Color(0xFF10B981) : const Color(0xFFEF4444),
              size: 40,
            ),
            const SizedBox(height: 12),
            Text(
              isPositive ? 'شكراً لتقييمك! 🎉' : 'نأسف لعدم رضاك 😔',
              style: TextStyle(
                fontWeight: FontWeight.bold,
                fontSize: 16,
                color: theme.textTheme.bodyLarge?.color,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              'هل تريد إضافة تعليق؟ (اختياري)',
              style: TextStyle(
                fontSize: 13,
                color: theme.hintColor,
              ),
            ),
            const SizedBox(height: 16),
            TextField(
              controller: _feedbackController,
              maxLines: 3,
              textDirection: TextDirection.rtl,
              decoration: InputDecoration(
                hintText: 'اكتب تعليقك هنا...',
                hintStyle: TextStyle(color: theme.hintColor.withOpacity(0.5)),
                filled: true,
                fillColor: theme.scaffoldBackgroundColor,
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: BorderSide(color: theme.dividerColor),
                ),
                enabledBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: BorderSide(color: theme.dividerColor.withOpacity(0.3)),
                ),
                focusedBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: BorderSide(color: theme.primaryColor, width: 1.5),
                ),
              ),
            ),
            const SizedBox(height: 20),
            Row(
              children: [
                Expanded(
                  child: OutlinedButton(
                    style: OutlinedButton.styleFrom(
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                      side: BorderSide(color: theme.dividerColor),
                      padding: const EdgeInsets.symmetric(vertical: 12),
                    ),
                    onPressed: () {
                      widget.onSubmit(null);
                      Navigator.pop(context);
                    },
                    child: Text(
                      'تخطي',
                      style: TextStyle(color: theme.hintColor),
                    ),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: ElevatedButton(
                    style: ElevatedButton.styleFrom(
                      backgroundColor: theme.primaryColor,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                      padding: const EdgeInsets.symmetric(vertical: 12),
                    ),
                    onPressed: () {
                      widget.onSubmit(_feedbackController.text.trim().isNotEmpty
                          ? _feedbackController.text.trim()
                          : null);
                      Navigator.pop(context);
                    },
                    child: const Text(
                      'إرسال',
                      style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold),
                    ),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}
