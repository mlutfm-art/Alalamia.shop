import 'package:flutter/material.dart';
import 'package:flutter_sixvalley_ecommerce/utill/app_constants.dart';
import 'package:shared_preferences/shared_preferences.dart';

class ThemeController with ChangeNotifier {
  final SharedPreferences? sharedPreferences;
  ThemeController({required this.sharedPreferences}) {
    _loadCurrentTheme();
  }

  bool _darkTheme = false;
  bool get darkTheme => _darkTheme;

  void toggleTheme() {
    _darkTheme = !_darkTheme;
    sharedPreferences!.setBool(AppConstants.theme, _darkTheme);
    notifyListeners();
  }

  void _loadCurrentTheme() async {
    _darkTheme = sharedPreferences!.getBool(AppConstants.theme) ?? false;
    notifyListeners();
  }

  Color? selectedPrimaryColor;
  Color? selectedSecondaryColor;

  void setThemeColor({Color? primaryColor, Color? secondaryColor}) {
    selectedPrimaryColor = primaryColor;
    selectedSecondaryColor = secondaryColor; // تم تصحيح الخطأ هنا
    notifyListeners();
  }

  /// دالة لتحسين وضوح الألوان (زيادة التشبع والتباين)
  Color enhanceColor(Color color, {double saturationIncrease = 0.15, double lightnessDecrease = 0.05}) {
    final HSLColor hsl = HSLColor.fromColor(color);
    return hsl
        .withSaturation((hsl.saturation + saturationIncrease).clamp(0.0, 1.0))
        .withLightness((hsl.lightness - lightnessDecrease).clamp(0.0, 1.0))
        .toColor();
  }
}

/// إضافة Extension لتسهيل جعل أي لون أكثر حيوية (Vivid) في أي مكان في التطبيق
extension ColorVibrancy on Color {
  Color get vived {
    final HSLColor hsl = HSLColor.fromColor(this);
    return hsl
        .withSaturation((hsl.saturation + 0.20).clamp(0.0, 1.0)) // زيادة التشبع بنسبة 20%
        .withLightness((hsl.lightness - 0.05).clamp(0.0, 1.0)) // تقليل الإضاءة قليلاً للتباين
        .toColor();
  }
}
