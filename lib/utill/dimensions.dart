import 'package:flutter_sixvalley_ecommerce/helper/responsive_helper.dart';
import 'package:flutter_sixvalley_ecommerce/main.dart';

class Dimensions {
  // Typography - Fine-tuned for readability
  static double fontSizeExtraSmall = ResponsiveHelper.isTab(Get.context!) ? 14 : 11.0;
  static double fontSizeSmall = ResponsiveHelper.isTab(Get.context!) ? 16 : 13.0;
  static double fontSizeDefault = ResponsiveHelper.isTab(Get.context!) ? 18 : 14.0;
  static double fontSizeLarge = ResponsiveHelper.isTab(Get.context!) ? 22 : 16.0;
  static double fontSizeExtraLarge = ResponsiveHelper.isTab(Get.context!) ? 26 : 18.0;
  static double fontSizeOverLarge = ResponsiveHelper.isTab(Get.context!) ? 30 : 24.0;
  static const double fontSizeWallet = 24.0;

  // Padding & Spacing - More "Airy" design
  static const double paddingSizeExtraExtraSmall = 4.0;
  static const double paddingSizeExtraSmall = 8.0;
  static const double paddingSizeEight = 8.0;
  static const double paddingSizeSmall = 12.0;
  static const double paddingSizeTwelve = 12.0;
  static const double paddingSizeDefault = 18.0;
  static const double homePagePadding = 20.0;
  static const double paddingSizeDefaultAddress = 20.0;
  static const double paddingSizeLarge = 24.0;
  static const double paddingSizeExtraLarge = 32.0;
  static const double paddingSizeThirtyFive = 35.0;
  static const double paddingSizeOverLarge = 56.0;
  static const double paddingSizeExtraOverLarge = 40.0;
  static const double paddingSizeButton = 45.0;

  // Margins
  static const double marginSizeExtraSmall = 8.0;
  static const double marginSizeSmall = 12.0;
  static const double marginSizeDefault = 18.0;
  static const double marginSizeLarge = 24.0;
  static const double marginSizeExtraLarge = 32.0;
  static const double marginSizeAuthSmall = 32.0;
  static const double marginSizeAuth = 56.0;

  // Icons
  static const double iconSizeExtraSmall = 14.0;
  static const double iconSizeSmall = 20.0;
  static const double iconSizeDefault = 26.0;
  static const double iconSizeLarge = 34.0;
  static const double iconSizeExtraLarge = 52.0;

  // Shapes & Radius - Soft UI Approach
  static const double radiusSmall = 8.0;
  static const double radiusDefault = 12.0;
  static const double radiusLarge = 18.0;
  static const double radiusExtraLarge = 24.0;
  
  // Specific Widget Sizes
  static const double imageSizeExtraSeventy = 75.0;
  static const double bannerPadding = 45.0;
  static const double topSpace = 35.0;
  static const double splashLogoWidth = 160.0;
  static const double chooseReviewImageSize = 45.0;
  static const double profileImageSize = 110.0;
  static const double logoHeight = 90.0;
  static const double cardHeight = 280.0;
  static const double menuIconSize = 28.0;
  static const double featuredProductCard = 380.0;
  static const double compareCardWidget = 210.0;
  static const double clearanceHomeTitleHeight = 65.0;
}
