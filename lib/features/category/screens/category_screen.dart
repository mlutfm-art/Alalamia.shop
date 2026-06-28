import 'package:flutter/material.dart';
import 'package:flutter_sixvalley_ecommerce/features/category/controllers/category_controller.dart';
import 'package:flutter_sixvalley_ecommerce/features/category/widgets/category_shimmer_widget.dart';
import 'package:flutter_sixvalley_ecommerce/features/category/widgets/category_widget.dart';
import 'package:flutter_sixvalley_ecommerce/localization/language_constrants.dart';
import 'package:flutter_sixvalley_ecommerce/utill/dimensions.dart';
import 'package:flutter_sixvalley_ecommerce/common/basewidget/custom_app_bar_widget.dart';
import 'package:provider/provider.dart';
import 'package:flutter_sixvalley_ecommerce/helper/route_healper.dart';

class CategoryScreen extends StatefulWidget {
  const CategoryScreen({super.key});

  @override
  State<CategoryScreen> createState() => _CategoryScreenState();
}

class _CategoryScreenState extends State<CategoryScreen> {
  @override
  void initState() {
    super.initState();
    Provider.of<CategoryController>(context, listen: false).getCategoryList(false);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: CustomAppBar(title: getTranslated('CATEGORY', context)),
      body: Consumer<CategoryController>(
        builder: (context, categoryProvider, child) {
          return categoryProvider.categoryList.isNotEmpty ? 
          GridView.builder(
            padding: const EdgeInsets.all(Dimensions.paddingSizeSmall),
            itemCount: categoryProvider.categoryList.length,
            gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
              crossAxisCount: 3,
              childAspectRatio: (1/1.2),
              mainAxisSpacing: 10,
              crossAxisSpacing: 10,
            ),
            itemBuilder: (BuildContext context, int index) {
              return InkWell(
                onTap: () {
                  RouterHelper.getBrandCategoryRoute(
                    action: RouteAction.push,
                    isBrand: false,
                    id: categoryProvider.categoryList[index].id,
                    name: categoryProvider.categoryList[index].name,
                  );
                },
                child: CategoryWidget(
                  category: categoryProvider.categoryList[index],
                  index: index,
                  length: categoryProvider.categoryList.length,
                ),
              );
            },
          ) : const CategoryShimmerWidget();
        },
      ),
    );
  }
}
