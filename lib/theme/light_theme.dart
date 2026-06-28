import 'package:flutter/material.dart';

// هويّة الصحة والجمال الفاخرة - نظام الألوان الموحد
Color _primaryColor = const Color(0xFF5CA609);
Color _secondaryColor = const Color(0xFFF8BBD0); 

ThemeData light({Color? primaryColor, Color? secondaryColor}) => ThemeData(
  fontFamily: 'Cairo', // الخط المعتمد للواجهات الحديثة
  primaryColor: primaryColor ?? _primaryColor,
  brightness: Brightness.light,
  highlightColor: Colors.white,
  hintColor: const Color(0xFF777777), // Text Secondary
  splashColor: Colors.transparent,
  cardColor: Colors.white,
  scaffoldBackgroundColor: const Color(0xFFFAFAFA), // خلفية ناعمة جداً

  textTheme: const TextTheme(
    displayLarge: TextStyle(color: Color(0xFF222222), fontWeight: FontWeight.bold, fontSize: 22),
    displayMedium: TextStyle(color: Color(0xFF222222), fontWeight: FontWeight.bold, fontSize: 20),
    titleLarge: TextStyle(color: Color(0xFF222222), fontWeight: FontWeight.w700, fontSize: 18),
    titleMedium: TextStyle(color: Color(0xFF222222), fontWeight: FontWeight.w600, fontSize: 16),
    bodyLarge: TextStyle(color: Color(0xFF222222), fontWeight: FontWeight.w500, fontSize: 14, height: 1.4), // Text Primary
    bodyMedium: TextStyle(color: Color(0xFF444444), fontWeight: FontWeight.w400, fontSize: 13, height: 1.4), // Content
    bodySmall: TextStyle(color: Color(0xFF777777), fontWeight: FontWeight.w400, fontSize: 11, height: 1.2), // Text Secondary
    labelLarge: TextStyle(color: Colors.white, fontWeight: FontWeight.w600, fontSize: 14),
  ),

  colorScheme: ColorScheme.light(
    primary: primaryColor ?? _primaryColor,
    secondary: secondaryColor ?? _secondaryColor,
    tertiary: const Color(0xFFFFF9C4), // كريمي خفيف جداً للعروض
    surface: const Color(0xFFFFFFFF),
    onPrimary: Colors.white, // أبيض فوق الأخضر للوضوح
    onSecondary: const Color(0xFF222222), // أسود فوق الوردي الناعم للرقي والتباين
    error: const Color(0xFFD32F2F),
    onSurface: const Color(0xFF222222),
    outline: const Color(0xFFE0E0E0),
    shadow: Colors.black.withOpacity(0.04),
    primaryContainer: const Color(0xFFF1F8E9),
    secondaryContainer: const Color(0xFFFDF2F5),
  ),

  appBarTheme: const AppBarTheme(
    backgroundColor: Colors.white,
    elevation: 0,
    centerTitle: true,
    titleTextStyle: TextStyle(color: Color(0xFF222222), fontSize: 18, fontWeight: FontWeight.w700, fontFamily: 'Cairo'),
    iconTheme: IconThemeData(color: Color(0xFF222222), size: 24),
  ),

  cardTheme: CardThemeData(
    color: Colors.white,
    elevation: 0,
    margin: EdgeInsets.zero,
    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)), // Soft UI Radius
  ),

  dividerTheme: const DividerThemeData(thickness: 0.5, color: Color(0xFFF1F1F1)),

  pageTransitionsTheme: const PageTransitionsTheme(builders: {
    TargetPlatform.android: CupertinoPageTransitionsBuilder(),
    TargetPlatform.iOS: ZoomPageTransitionsBuilder(),
    TargetPlatform.fuchsia: ZoomPageTransitionsBuilder(),
  }),
);
