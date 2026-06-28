import 'package:flutter/material.dart';

// هويّة الصحة والجمال الفاخرة - نظام الألوان الداكن
Color _primaryColor = const Color(0xFF8BC34A); 
Color _secondaryColor = const Color(0xFFF8BBD0); 

ThemeData dark = ThemeData(
  fontFamily: 'Cairo',
  primaryColor: _primaryColor,
  brightness: Brightness.dark,
  highlightColor: const Color(0xFF2C2D30),
  hintColor: const Color(0xFFB0B3B8), 
  cardColor: const Color(0xFF1E1E1E), // طبقة البطاقات فوق الخلفية
  scaffoldBackgroundColor: const Color(0xFF121212), // المعيار الاحترافي للوضع الليلي
  splashColor: Colors.transparent,

  textTheme: const TextTheme(
    displayLarge: TextStyle(color: Color(0xFFE4E6EB), fontWeight: FontWeight.bold, fontSize: 20),
    displayMedium: TextStyle(color: Color(0xFFE4E6EB), fontWeight: FontWeight.bold, fontSize: 18),
    titleLarge: TextStyle(color: Color(0xFFE4E6EB), fontWeight: FontWeight.w700, fontSize: 16),
    titleMedium: TextStyle(color: Color(0xFFE4E6EB), fontWeight: FontWeight.w600, fontSize: 14),
    bodyLarge: TextStyle(color: Color(0xFFE4E6EB), fontWeight: FontWeight.w500, fontSize: 13, height: 1.4), // نص أساسي
    bodyMedium: TextStyle(color: Color(0xFFB0B3B8)), // تباين متوسط
    bodySmall: TextStyle(color: Color(0xFF8A8D91)), // نصوص ثانوية
  ),

  colorScheme: ColorScheme.dark(
    primary: _primaryColor,
    secondary: _secondaryColor,
    tertiary: const Color(0xFF3E331F),
    surface: const Color(0xFF1E1E1E),
    onPrimary: Colors.black, // نص أسود فوق الأخضر لتباين مريح في الوضع الداكن
    onSecondary: Colors.black, 
    error: const Color(0xFFFFB4AB),
    outline: const Color(0xFF3E3E3E),
    shadow: Colors.black.withOpacity(0.5),
    primaryContainer: const Color(0xFF2E4D1A),
    secondaryContainer: const Color(0xFF4A1D2C),
  ),

  appBarTheme: const AppBarTheme(
    backgroundColor: Color(0xFF1E1E1E),
    elevation: 0,
    centerTitle: true,
    titleTextStyle: TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.w700, fontFamily: 'Cairo'),
    iconTheme: IconThemeData(color: Colors.white, size: 22),
  ),

  cardTheme: CardThemeData(
    color: const Color(0xFF1E1E1E),
    elevation: 0,
    margin: EdgeInsets.zero,
    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
  ),

  dividerTheme: const DividerThemeData(thickness: 0.5, color: Color(0xFF2C2C2C)),

  pageTransitionsTheme: const PageTransitionsTheme(builders: {
    TargetPlatform.android: ZoomPageTransitionsBuilder(),
    TargetPlatform.iOS: ZoomPageTransitionsBuilder(),
    TargetPlatform.fuchsia: ZoomPageTransitionsBuilder(),
  }),
);
