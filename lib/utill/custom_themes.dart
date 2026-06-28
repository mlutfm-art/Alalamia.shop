import 'package:flutter/material.dart';
import 'package:flutter_sixvalley_ecommerce/theme/controllers/theme_controller.dart';
import 'package:flutter_sixvalley_ecommerce/utill/dimensions.dart';
import 'package:provider/provider.dart';

// يتم استخدام Tajawal كخط أساسي للتطبيق لضمان الوضوح والاحترافية
const titilliumRegular = TextStyle(
  fontFamily: 'Tajawal',
  fontWeight: FontWeight.w400,
  fontSize: 12,
);

const titleRegular = TextStyle(
  fontFamily: 'Tajawal',
  fontWeight: FontWeight.w500,
  fontSize: 14,
);

// نمط عناوين الأقسام الرئيسية - كبيرة وبارزة
const titleHeader = TextStyle(
  fontFamily: 'Tajawal',
  fontWeight: FontWeight.w800, 
  fontSize: 18, // سيتم التحكم به عبر Dimensions غالباً
);

const titilliumSemiBold = TextStyle(
  fontFamily: 'Tajawal',
  fontSize: 12,
  fontWeight: FontWeight.w600,
);

const titilliumBold = TextStyle(
  fontFamily: 'Tajawal',
  fontSize: 14,
  fontWeight: FontWeight.w700,
);

const textRegular = TextStyle(
  fontFamily: 'Tajawal',
  fontWeight: FontWeight.w400,
  fontSize: 14,
);

// الخط المخصص لأسماء المنتجات في الكروت - صغير وواضح
const textMedium = TextStyle(
  fontFamily: 'Tajawal',
  fontSize: 14,
  fontWeight: FontWeight.w600 
);

const textBold = TextStyle(
  fontFamily: 'Tajawal',
  fontSize: 14,
  fontWeight: FontWeight.w700
);

// نمط أسعار المنتجات - بارز جداً لسهولة المسح البصري
const robotoBold = TextStyle(
  fontFamily: 'Tajawal',
  fontSize: 14,
  fontWeight: FontWeight.w900,
);

class ThemeShadow {
  static List <BoxShadow> getShadow(BuildContext context) {
    List<BoxShadow> boxShadow =  [BoxShadow(color: Provider.of<ThemeController>(context, listen: false).darkTheme? Colors.black26:
    Theme.of(context).primaryColor.withOpacity(0.075), blurRadius: 5,spreadRadius: 1,offset: const Offset(1,1))];
    return boxShadow;
  }
}
