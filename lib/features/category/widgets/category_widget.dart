import 'package:flutter/material.dart';
import 'package:flutter_sixvalley_ecommerce/features/category/domain/models/category_model.dart';
import 'package:flutter_sixvalley_ecommerce/localization/controllers/localization_controller.dart';
import 'package:flutter_sixvalley_ecommerce/utill/custom_themes.dart';
import 'package:flutter_sixvalley_ecommerce/utill/dimensions.dart';
import 'package:flutter_sixvalley_ecommerce/common/basewidget/custom_image_widget.dart';
import 'package:provider/provider.dart';

class CategoryWidget extends StatelessWidget {
  final CategoryModel category;
  final int index;
  final int length;
  const CategoryWidget({super.key, required this.category, required this.index, required this.length});

  @override
  Widget build(BuildContext context) {
    bool isLtr = Provider.of<LocalizationController>(context, listen: false).isLtr;
    int homeLength = length >= 10 ? 10 : length;

    return Padding(
      padding: EdgeInsets.only(
        left: isLtr ? (index == 0 ? Dimensions.paddingSizeDefault : Dimensions.paddingSizeSmall) : 0,
        right: isLtr ? (index + 1 == homeLength ? Dimensions.paddingSizeDefault : 0) : (index == 0 ? Dimensions.paddingSizeDefault : Dimensions.paddingSizeSmall),
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Container(
            height: 70, width: 70, // Slightly more compact and standard size
            decoration: BoxDecoration(
              color: Theme.of(context).cardColor,
              borderRadius: BorderRadius.circular(14), // Modern Radius 14
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withOpacity(0.04),
                  spreadRadius: 1,
                  blurRadius: 8,
                  offset: const Offset(0, 2),
                )
              ],
              border: Border.all(
                color: Theme.of(context).primaryColor.withOpacity(0.08),
                width: 1,
              ),
            ),
            child: ClipRRect(
              borderRadius: BorderRadius.circular(14),
              child: Padding(
                padding: const EdgeInsets.all(8.0), // Padding for the icon within the card
                child: CustomImageWidget(
                  image: '${category.imageFullUrl?.path}',
                  fit: BoxFit.contain, // Better for icons/category images
                ),
              ),
            ),
          ),
          const SizedBox(height: 10),
          SizedBox(
            width: 75,
            child: Text(
              category.name ?? '',
              textAlign: TextAlign.center,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              style: textMedium.copyWith(
                fontSize: Dimensions.fontSizeSmall,
                fontWeight: FontWeight.w500,
                color: Theme.of(context).textTheme.bodyLarge?.color?.withOpacity(0.8),
              ),
            ),
          ),
        ],
      ),
    );
  }
}
