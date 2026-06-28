import 'package:flutter/material.dart';
import 'package:flutter_sixvalley_ecommerce/common/basewidget/title_row_widget.dart';
import 'package:flutter_sixvalley_ecommerce/features/address/controllers/address_controller.dart';
import 'package:flutter_sixvalley_ecommerce/features/auth/controllers/auth_controller.dart';
import 'package:flutter_sixvalley_ecommerce/features/banner/controllers/banner_controller.dart';
import 'package:flutter_sixvalley_ecommerce/features/banner/widgets/banners_widget.dart';
import 'package:flutter_sixvalley_ecommerce/features/banner/widgets/footer_banner_slider_widget.dart';
import 'package:flutter_sixvalley_ecommerce/features/banner/widgets/single_banner_widget.dart';
import 'package:flutter_sixvalley_ecommerce/features/brand/controllers/brand_controller.dart';
import 'package:flutter_sixvalley_ecommerce/features/brand/widgets/brand_list_widget.dart';
import 'package:flutter_sixvalley_ecommerce/features/cart/controllers/cart_controller.dart';
import 'package:flutter_sixvalley_ecommerce/features/category/controllers/category_controller.dart';
import 'package:flutter_sixvalley_ecommerce/features/category/widgets/category_list_widget.dart';
import 'package:flutter_sixvalley_ecommerce/features/clearance_sale/widgets/clearance_sale_list_widget.dart';
import 'package:flutter_sixvalley_ecommerce/features/deal/controllers/featured_deal_controller.dart';
import 'package:flutter_sixvalley_ecommerce/features/deal/controllers/flash_deal_controller.dart';
import 'package:flutter_sixvalley_ecommerce/features/deal/widgets/featured_deal_list_widget.dart';
import 'package:flutter_sixvalley_ecommerce/features/deal/widgets/flash_deals_list_widget.dart';
import 'package:flutter_sixvalley_ecommerce/features/home/shimmers/flash_deal_shimmer.dart';
import 'package:flutter_sixvalley_ecommerce/features/home/widgets/announcement_widget.dart';
import 'package:flutter_sixvalley_ecommerce/features/home/widgets/aster_theme/find_what_you_need_shimmer.dart';
import 'package:flutter_sixvalley_ecommerce/features/home/widgets/featured_product_widget.dart';
import 'package:flutter_sixvalley_ecommerce/features/home/widgets/product_list_widget.dart';
import 'package:flutter_sixvalley_ecommerce/features/home/widgets/product_type_popup_menu_widget.dart';
import 'package:flutter_sixvalley_ecommerce/features/home/widgets/search_home_page_widget.dart';
import 'package:flutter_sixvalley_ecommerce/features/notification/controllers/notification_controller.dart';
import 'package:flutter_sixvalley_ecommerce/features/prediction/controllers/prediction_controller.dart';
import 'package:flutter_sixvalley_ecommerce/features/prediction/widgets/prediction_banner_widget.dart';
import 'package:flutter_sixvalley_ecommerce/features/botcenter/widgets/bot_header_icon_widget.dart';
import 'package:flutter_sixvalley_ecommerce/features/product/controllers/product_controller.dart';
import 'package:flutter_sixvalley_ecommerce/features/product/widgets/home_category_product_widget.dart';
import 'package:flutter_sixvalley_ecommerce/features/product/widgets/latest_product_list_widget.dart';
import 'package:flutter_sixvalley_ecommerce/features/product/widgets/recommended_product_widget.dart';
import 'package:flutter_sixvalley_ecommerce/features/profile/controllers/profile_contrroller.dart';
import 'package:flutter_sixvalley_ecommerce/features/shop/controllers/shop_controller.dart';
import 'package:flutter_sixvalley_ecommerce/features/home/widgets/top_seller_widget.dart';
import 'package:flutter_sixvalley_ecommerce/features/smartads/controllers/ad_controller.dart';
import 'package:flutter_sixvalley_ecommerce/features/smartads/widgets/in_app_banner_dialog.dart';
import 'package:flutter_sixvalley_ecommerce/features/smartads/widgets/smart_ad_banner_widget.dart';
import 'package:flutter_sixvalley_ecommerce/features/splash/controllers/splash_controller.dart';
import 'package:flutter_sixvalley_ecommerce/features/splash/domain/models/config_model.dart';
import 'package:flutter_sixvalley_ecommerce/helper/responsive_helper.dart';
import 'package:flutter_sixvalley_ecommerce/helper/route_healper.dart';
import 'package:flutter_sixvalley_ecommerce/localization/language_constrants.dart';
import 'package:flutter_sixvalley_ecommerce/main.dart';
import 'package:flutter_sixvalley_ecommerce/theme/controllers/theme_controller.dart';
import 'package:flutter_sixvalley_ecommerce/utill/custom_themes.dart';
import 'package:flutter_sixvalley_ecommerce/utill/dimensions.dart';
import 'package:flutter_sixvalley_ecommerce/utill/images.dart';
import 'package:provider/provider.dart';


class HomePage extends StatefulWidget {
  const HomePage({super.key});

  @override
  State<HomePage> createState() => _HomePageState();

  static Future<void> loadData(bool reload) async {
    Future.microtask(() async {
      final flashDealController = Provider.of<FlashDealController>(Get.context!, listen: false);
      final shopController = Provider.of<ShopController>(Get.context!, listen: false);
      final categoryController = Provider.of<CategoryController>(Get.context!, listen: false);
      final bannerController = Provider.of<BannerController>(Get.context!, listen: false);
      final addressController = Provider.of<AddressController>(Get.context!, listen: false);
      final productController = Provider.of<ProductController>(Get.context!, listen: false);
      final brandController = Provider.of<BrandController>(Get.context!, listen: false);
      final featuredDealController = Provider.of<FeaturedDealController>(Get.context!, listen: false);
      final notificationController = Provider.of<NotificationController>(Get.context!, listen: false);
      final cartController = Provider.of<CartController>(Get.context!, listen: false);
      final profileController = Provider.of<ProfileController>(Get.context!, listen: false);
      final splashController = Provider.of<SplashController>(Get.context!, listen: false);
      final predictionController = Provider.of<PredictionController>(Get.context!, listen: false);

      splashController.initConfig(Get.context!, null, null);
      categoryController.getCategoryList(reload);
      bannerController.getBannerList();
      shopController.getAllSellerList(offset: 1, isUpdate: reload);
      shopController.getTopSellerList(offset: 1, isUpdate: reload);
      addressController.getAddressList();
      cartController.getCartData(Get.context!);
      productController.getHomeCategoryProductList(reload);
      brandController.getBrandList(offset: 1, isUpdate: reload);
      featuredDealController.getFeaturedDealList();
      productController.getLatestProductList(1, isUpdate: reload);
      productController.getSelectedProductModel(1, isUpdate: reload);
      productController.getFeaturedProductModel(1, isUpdate: reload);
      productController.getRecommendedProduct();
      productController.getClearanceAllProductList(1, isUpdate: reload);
      
      predictionController.getPredictionBanner();
      predictionController.getMatchList();

      final adController = Provider.of<AdController>(Get.context!, listen: false);
      adController.getActiveAds(device: "android", region: "SA");
      adController.getPendingInAppBanners(null);

      if(notificationController.notificationModel == null || (notificationController.notificationModel != null && notificationController.notificationModel!.notification!.isEmpty) || reload) {
        notificationController.getNotificationList(1);
      }
      if(Provider.of<AuthController>(Get.context!, listen: false).isLoggedIn() && profileController.userInfoModel == null){
        await profileController.getUserInfo(Get.context!);
      }
    });
  }
}

class _HomePageState extends State<HomePage> {
  final ScrollController _scrollController = ScrollController();

  bool singleVendor = false;
  @override
  void initState() {
    super.initState();
    singleVendor = Provider.of<SplashController>(context, listen: false).configModel?.businessMode == "single";
    
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final adController = Provider.of<AdController>(context, listen: false);
      adController.getActiveAds(device: "android", region: "SA");
      adController.getPendingInAppBanners(null).then((_) {
        if (adController.pendingInAppBanners.isNotEmpty) {
          showDialog(
            context: context,
            barrierDismissible: true,
            builder: (context) => InAppBannerDialog(ad: adController.pendingInAppBanners.first),
          );
        }
      });
    });
  }

  Widget _buildSection({required Widget child, double? verticalPadding, bool hasMargin = true}) {
    return Container(
      margin: hasMargin ? const EdgeInsets.symmetric(horizontal: 12, vertical: 8) : EdgeInsets.zero,
      padding: EdgeInsets.symmetric(vertical: verticalPadding ?? 12),
      decoration: BoxDecoration(
        color: Theme.of(context).cardColor,
        borderRadius: BorderRadius.circular(15),
        border: Border.all(color: Theme.of(context).primaryColor.withOpacity(0.08), width: 1),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.04),
            spreadRadius: 0,
            blurRadius: 15,
            offset: const Offset(0, 8),
          )
        ],
      ),
      child: child,
    );
  }


  @override
  Widget build(BuildContext context) {
    final ConfigModel? configModel = Provider.of<SplashController>(context, listen: false).configModel;

    return Scaffold(
      backgroundColor: Theme.of(context).scaffoldBackgroundColor.withOpacity(0.98),
      resizeToAvoidBottomInset: false,
      body: SafeArea(child: RefreshIndicator(
        onRefresh: () async {
          await HomePage.loadData(true);
        },
        child: CustomScrollView(
          controller: _scrollController,
          slivers: [
            SliverAppBar(
              floating: true,
              elevation: 0,
              centerTitle: false,
              automaticallyImplyLeading: false,
              backgroundColor: Theme.of(context).highlightColor,
              titleSpacing: 15,
              title: Row(
                children: [
                  Image.asset(Images.logoWithNameImage, height: 35),
                  const SizedBox(width: 10),
                  const BotHeaderIconWidget(),
                  const SizedBox(width: 6),
                  Text(
                    "بوت العملاء",
                    style: textBold.copyWith(
                      fontSize: 10,
                      color: Theme.of(context).primaryColor,
                    ),
                  ),
                ],
              ),
            ),

            const SliverToBoxAdapter(child: SizedBox(height: 10)),

            SliverToBoxAdapter(child: Provider.of<SplashController>(context, listen: false).configModel!.announcement!.status == '1'?
            Consumer<SplashController>(
                builder: (context, announcement, _){
                  return (announcement.configModel!.announcement!.announcement != null && announcement.onOff)?
                  AnnouncementWidget(announcement: announcement.configModel!.announcement):const SizedBox();
                }): const SizedBox()),

            SliverPersistentHeader(pinned: true, delegate: SliverSearchDelegate(
              child: InkWell(
                onTap: ()=> RouterHelper.getSearchRoute(action: RouteAction.push),
                child: const Hero(tag: 'search', child: Material(child: SearchHomePageWidget())),
              ),
            )),

            // مسافة مريحة بعد شريط البحث
            const SliverToBoxAdapter(child: SizedBox(height: 40)),


            SliverToBoxAdapter(
              child: Padding(
                padding: const EdgeInsets.only(top: 0),
                child: const BannersWidget(),
              ),
            ),

            SliverToBoxAdapter(
              child: SmartAdBannerWidget(placement: 'home_top'),
            ),


            SliverToBoxAdapter(
              child: _buildSection(
                child: CategoryListWidget(isHomePage: true),
              ),
            ),

            SliverToBoxAdapter(
              child: Consumer<FlashDealController>(builder: (context, megaDeal, child) {
                return megaDeal.flashDeal == null ? const FlashDealShimmer()
                    : megaDeal.flashDealList.isNotEmpty ? _buildSection(
                      child: Column(children: [
                        Padding(
                          padding: const EdgeInsets.symmetric(horizontal: Dimensions.paddingSizeDefault),
                          child: TitleRowWidget(
                            title: getTranslated('flash_deal', context)?.toUpperCase(),
                            eventDuration: megaDeal.flashDeal != null ? megaDeal.duration : null,
                            onTap: () => RouterHelper.getFlashDealScreenViewRoute(),
                            isFlash: true,
                          ),
                        ),
                        const SizedBox(height: Dimensions.paddingSizeSmall),

                        Padding(
                          padding: const EdgeInsets.symmetric(horizontal: Dimensions.paddingSizeDefault),
                          child: Text(getTranslated('hurry_up_the_offer_is_limited_grab_while_it_lasts', context)??'',
                            style: textBold.copyWith(
                                color: Theme.of(context).primaryColor, 
                                fontSize: Dimensions.fontSizeLarge,
                                fontWeight: FontWeight.w900,
                                letterSpacing: 0.5
                            ),
                            textAlign: TextAlign.center,
                          ),
                        ),
                        const SizedBox(height: Dimensions.paddingSizeSmall),

                        const FlashDealsListWidget()
                      ])) : const SizedBox.shrink();
              }),
            ),


            SliverToBoxAdapter(
              child: Consumer<FeaturedDealController>(
                  builder: (context, featuredDealProvider, child) {
                    return (featuredDealProvider.featuredDealProductList != null && featuredDealProvider.featuredDealProductList!.isNotEmpty) ?
                    _buildSection(
                      verticalPadding: 0,
                      child: Column(
                        children: [
                          Stack(children: [
                            Container(
                              width: MediaQuery.of(context).size.width,
                              height: 150,
                              decoration: BoxDecoration(
                                color: Provider.of<ThemeController>(context, listen: false).darkTheme ?
                                Theme.of(context).highlightColor : Theme.of(context).colorScheme.onTertiary,
                                borderRadius: const BorderRadius.vertical(top: Radius.circular(Dimensions.radiusDefault)),
                              ),
                            ),
                            Column(children: [
                              Padding(
                                padding: const EdgeInsets.symmetric(vertical: Dimensions.paddingSizeDefault),
                                child: TitleRowWidget(
                                  title: '${getTranslated('featured_deals', context)}',
                                  onTap: () => RouterHelper.getFeaturedDealScreenViewRoute(),
                                ),
                              ),
                              const FeaturedDealsListWidget(),
                            ]),
                          ]),
                        ],
                      ),
                    ) : (featuredDealProvider.featuredDealProductList == null) ? const FindWhatYouNeedShimmer() : const SizedBox.shrink();
                  }
              ),
            ),


            SliverToBoxAdapter(
              child: _buildSection(
                child: const ClearanceListWidget(),
              ),
            ),


            SliverToBoxAdapter(
              child: Consumer<BannerController>(builder: (context, footerBannerProvider, child){
                return footerBannerProvider.footerBannerList != null && footerBannerProvider.footerBannerList!.isNotEmpty?
                _buildSection(
                  verticalPadding: 0,
                  child: ClipRRect(
                    borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
                    child: SingleBannersWidget( bannerModel : footerBannerProvider.footerBannerList?[0]),
                  ),
                ) : const SizedBox();
              }),
            ),


            SliverToBoxAdapter(
              child: _buildSection(
                child: const FeaturedProductWidget(),
              ),
            ),

            if(!singleVendor)
              SliverToBoxAdapter(
                child: _buildSection(
                  child: Column(
                    children: [
                      Consumer<ShopController>(
                          builder: (context, topSellerProvider, child) {
                            return (topSellerProvider.topSellerModel != null && (topSellerProvider.topSellerModel!.sellers!=null && topSellerProvider.topSellerModel!.sellers!.isNotEmpty))?
                            TitleRowWidget(title: getTranslated('top_seller', context),
                                onTap: ()=> RouterHelper.getAllTopSellerRoute(action: RouteAction.push, title: 'top_seller')) :
                            const SizedBox();
                          }),
                      const SizedBox(height: Dimensions.paddingSizeSmall),
                      Consumer<ShopController>(
                          builder: (context, topSellerProvider, child) {
                            return (topSellerProvider.topSellerModel != null && (topSellerProvider.topSellerModel!.sellers!=null && topSellerProvider.topSellerModel!.sellers!.isNotEmpty))?
                            SizedBox(height: ResponsiveHelper.isTab(context)? 170 : 150, child: const TopSellerWidget()):const SizedBox();
                          }
                      )
                    ],
                  ),
                ),
              ),

            SliverToBoxAdapter(
              child: SmartAdBannerWidget(placement: 'home_middle'),
            ),

            SliverToBoxAdapter(
              child: _buildSection(
                child: RecommendedProductWidget(),
              ),
            ),


            SliverToBoxAdapter(
              child: _buildSection(
                child: LatestProductListWidget(),
              ),
            ),


            if(configModel!.brandSetting == "1")
              SliverToBoxAdapter(
                  child: _buildSection(
                    child: const BrandListWidget(isHomePage: true),
                  )
              ),

            SliverToBoxAdapter(
              child: _buildSection(
                child: const HomeCategoryProductWidget(isHomePage: true),
              ),
            ),

            const SliverToBoxAdapter(child: SizedBox(height: 20)),
            const SliverToBoxAdapter(child: FooterBannerSliderWidget()),
            const SliverToBoxAdapter(child: SizedBox(height: 30)),

            SliverPersistentHeader(pinned: true, delegate: SliverDelegate(
              height: 50,
              child: Align(
                alignment: Alignment.topLeft,
                child: Container(color: Theme.of(context).scaffoldBackgroundColor, child: const ProductPopupFilterWidget()),
              ),
            )),

            HomeProductListWidget(scrollController: _scrollController),

            const SliverToBoxAdapter(child: SizedBox(height: 50)),
          ],
        ),
      ),
      ),
    );
  }
}

class SliverDelegate extends SliverPersistentHeaderDelegate {
  Widget child;
  double height;
  SliverDelegate({required this.child, this.height = 50});

  @override
  Widget build(BuildContext context, double shrinkOffset, bool overlapsContent) {
    return child;
  }

  @override
  double get maxExtent => height;

  @override
  double get minExtent => height;

  @override
  bool shouldRebuild(SliverDelegate oldDelegate) {
    return oldDelegate.maxExtent != height || oldDelegate.minExtent != height || child != oldDelegate.child;
  }
}


class SliverSearchDelegate extends SliverPersistentHeaderDelegate {
  Widget child;
  double height;
  SliverSearchDelegate({required this.child, this.height = 70});

  @override
  Widget build(BuildContext context, double shrinkOffset, bool overlapsContent) {
    return child;
  }

  @override
  double get maxExtent => height;

  @override
  double get minExtent => height;

  @override
  bool shouldRebuild(covariant SliverSearchDelegate oldDelegate) {
    return oldDelegate.height != height || oldDelegate.child != child;
  }
}
