import 'package:flutter/material.dart';
import 'package:flutter_sixvalley_ecommerce/features/deal/controllers/flash_deal_controller.dart';
import 'package:flutter_sixvalley_ecommerce/features/product_details/controllers/product_details_controller.dart';
import 'package:flutter_sixvalley_ecommerce/features/product_details/widgets/bottom_cart_widget.dart';
import 'package:flutter_sixvalley_ecommerce/features/product_details/widgets/product_image_widget.dart';
import 'package:flutter_sixvalley_ecommerce/features/product_details/widgets/product_specification_widget.dart';
import 'package:flutter_sixvalley_ecommerce/features/product_details/widgets/product_title_widget.dart';
import 'package:flutter_sixvalley_ecommerce/features/product_details/widgets/youtube_video_widget.dart';
import 'package:flutter_sixvalley_ecommerce/common/basewidget/custom_app_bar_widget.dart';
import 'package:flutter_sixvalley_ecommerce/features/home/shimmers/product_details_shimmer.dart';
import 'package:flutter_sixvalley_ecommerce/features/shop/controllers/shop_controller.dart';
import 'package:flutter_sixvalley_ecommerce/helper/product_helper.dart';
import 'package:flutter_sixvalley_ecommerce/helper/route_healper.dart';
import 'package:flutter_sixvalley_ecommerce/localization/language_constrants.dart';
import 'package:flutter_sixvalley_ecommerce/utill/custom_themes.dart';
import 'package:flutter_sixvalley_ecommerce/utill/dimensions.dart';
import 'package:provider/provider.dart';

class ProductDetails extends StatefulWidget {
  final int? productId;
  final String? slug;
  final bool isFromWishList;
  final bool isNotification;
  final bool fromFlashDeals;
  const ProductDetails({super.key, required this.productId, required this.slug, this.isFromWishList = false, this.isNotification = false, this.fromFlashDeals = false});

  @override
  State<ProductDetails> createState() => _ProductDetailsState();
}

class _ProductDetailsState extends State<ProductDetails> {

  Future<void> _loadData(BuildContext context) async {
    Provider.of<ProductDetailsController>(context, listen: false).getProductDetails(context, widget.productId.toString(), widget.slug.toString());
    Provider.of<ProductDetailsController>(context, listen: false).removePrevLink();
    
    Provider.of<ProductDetailsController>(context, listen: false).getCount(widget.productId.toString(), context);
    Provider.of<ProductDetailsController>(context, listen: false).getSharableLink(widget.slug.toString(), context);
    Provider.of<ProductDetailsController>(context, listen: false).setImageSliderSelectedIndex(0, isUpdate: false);
    Provider.of<ShopController>(context, listen: false).emptyProductDetailsSeller();
    Provider.of<FlashDealController>(context, listen: false).getFlashDealList(false, false);
  }

  @override
  void initState() {
    Provider.of<ProductDetailsController>(context, listen: false).selectReviewSection(false, isUpdate: false);
    _loadData(context);
    super.initState();
  }

  @override
  Widget build(BuildContext context) {
    return PopScope(
      canPop: Navigator.canPop(context),
      onPopInvokedWithResult: (didPop, result) async {
        if (widget.isNotification) {
          RouterHelper.getDashboardRoute(action: RouteAction.pushNamedAndRemoveUntil);
        }
      },
      child: Scaffold(
        appBar: CustomAppBar(
          title: getTranslated('product_details', context),
          onBackPressed: () {
            if (Navigator.of(context).canPop()) {
              Navigator.of(context).pop();
            } else {
              RouterHelper.getDashboardRoute(action: RouteAction.pushNamedAndRemoveUntil);
            }
          },
        ),
        body: RefreshIndicator(
          onRefresh: () async => _loadData(context),
          child: Consumer<ProductDetailsController>(
            builder: (context, details, child) {

              List<TextSpan> authors = [];
              if(details.productDetailsModel?.authors != null && details.productDetailsModel!.authors!.isNotEmpty) {
                for(int i=0; i < details.productDetailsModel!.authors!.length; i++) {
                  authors.add(TextSpan(text: '${details.productDetailsModel!.authors![i]}${i == details.productDetailsModel!.authors!.length - 1 ? '' : ', '} ',
                    style: titilliumSemiBold.copyWith(color: Theme.of(context).primaryColor, fontSize: Dimensions.fontSizeDefault),
                  ));
                }
              }

              List<TextSpan> publishingHouses = [];
              if(details.productDetailsModel?.publishingHouse != null && details.productDetailsModel!.publishingHouse!.isNotEmpty) {
                for(int i=0; i < details.productDetailsModel!.publishingHouse!.length; i++) {
                  publishingHouses.add(TextSpan(text: '${details.productDetailsModel!.publishingHouse![i]}${i == details.productDetailsModel!.publishingHouse!.length - 1 ? '' : ', '} ',
                    style: titilliumSemiBold.copyWith(color: Theme.of(context).primaryColor, fontSize: Dimensions.fontSizeDefault),
                  ));
                }
              }

              return SingleChildScrollView(
                physics: const BouncingScrollPhysics(),
                child: !details.isDetails ?
                Column(children: [
                  ProductImageWidget(productModel: details.productDetailsModel, fromFlashDeals: widget.fromFlashDeals),
                  Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                    ProductTitleWidget(
                      productModel: details.productDetailsModel,
                      averageRatting: "0",
                    ),

                    if(authors.isNotEmpty || publishingHouses.isNotEmpty)
                    Container(
                      width: double.infinity,
                      margin: const EdgeInsets.only(top: Dimensions.paddingSizeSmall),
                      padding: const EdgeInsets.symmetric(horizontal: Dimensions.paddingSizeDefault, vertical: Dimensions.paddingSizeSmall),
                      decoration: BoxDecoration(color: Theme.of(context).cardColor),
                      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                        if(authors.isNotEmpty)
                        Padding(
                          padding: const EdgeInsets.only(bottom: Dimensions.paddingSizeExtraSmall),
                          child: Text.rich(TextSpan(children: [
                            TextSpan(text: '${getTranslated('author', context)}: ', style: textRegular.copyWith(fontSize: Dimensions.fontSizeDefault)),
                            ...authors,
                          ])),
                        ),

                        if(publishingHouses.isNotEmpty)
                        Text.rich(TextSpan(children: [
                          TextSpan(text: '${getTranslated('publishing_house', context)}: ', style: textRegular.copyWith(fontSize: Dimensions.fontSizeDefault)),
                          ...publishingHouses,
                        ])),
                      ]),
                    ),

                    const SizedBox(height: Dimensions.paddingSizeDefault),

                    Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                      (details.productDetailsModel?.details != null && details.productDetailsModel!.details!.isNotEmpty) ?
                      Container(
                        width: double.infinity,
                        decoration: BoxDecoration(color: Theme.of(context).cardColor),
                        padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
                        child: ProductSpecificationWidget(
                          productSpecification: ProductHelper.removeIframe(details.productDetailsModel!.details ?? ''),
                        ),
                      ) : const SizedBox(),

                      if (details.productDetailsModel?.videoUrl != null && details.isValidYouTubeUrl(details.productDetailsModel!.videoUrl!))
                        Padding(
                          padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
                          child: YoutubeVideoWidget(url: details.productDetailsModel!.videoUrl),
                        ),
                    ]),
                  ]),
                  const SizedBox(height: 50),
                ]) :
                const ProductDetailsShimmer(),
              );
            },
          ),
        ),
        bottomNavigationBar: Consumer<ProductDetailsController>(
          builder: (context, details, child) {
            return !details.isDetails ? BottomCartWidget(product: details.productDetailsModel) : const SizedBox();
          }
        ),
      ),
    );
  }
}
