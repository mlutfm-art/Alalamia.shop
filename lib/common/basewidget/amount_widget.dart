import 'package:flutter/material.dart';
import 'package:flutter_sixvalley_ecommerce/utill/custom_themes.dart';
import 'package:flutter_sixvalley_ecommerce/utill/dimensions.dart';

class AmountWidget extends StatelessWidget {
  final String? title;
  final String amount;
  final bool? isTitleBlack;
  final double? fontSize;
  const AmountWidget({super.key, required this.title, required this.amount, this.isTitleBlack = false, this.fontSize});

  @override
  Widget build(BuildContext context) {
    return Padding(padding: const EdgeInsets.symmetric(vertical: Dimensions.paddingSizeExtraSmall),
      child: Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
        Expanded(
          child: Text(title!, style: textMedium.copyWith(
            fontSize: fontSize ?? Dimensions.fontSizeDefault, 
            color:(!isTitleBlack!) ? Theme.of(context).textTheme.titleMedium!.color : Theme.of(context).textTheme.bodyLarge?.color 
          )),
        ),
        const SizedBox(width: Dimensions.paddingSizeExtraSmall),
        Text(amount, style: textMedium.copyWith(
          fontSize: fontSize ?? Dimensions.fontSizeDefault, 
          color: Theme.of(context).textTheme.bodyLarge?.color
        )),
      ]),
    );
  }
}
