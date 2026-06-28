
import 'package:flutter/material.dart';
import 'package:flutter_sixvalley_ecommerce/features/address/domain/models/address_model.dart';
import 'package:flutter_sixvalley_ecommerce/utill/custom_themes.dart';
import 'package:flutter_sixvalley_ecommerce/utill/dimensions.dart';
import 'package:flutter_sixvalley_ecommerce/utill/images.dart';

class AddressTypeWidget extends StatelessWidget {
  final AddressModel? address;
  const AddressTypeWidget({super.key, this.address});

  @override
  Widget build(BuildContext context) {
    String addressType = address?.addressType?.toLowerCase() ?? 'others';
    
    return ListTile(
      leading: Image.asset(
        addressType == 'home' ? Images.homeImage
        : addressType == 'office' ? Images.officeImage 
        : Images.address,
        color: Theme.of(context).textTheme.bodyLarge?.color, 
        height: 30, width: 30
      ),
      title: Text(
        address?.address ?? '',
        maxLines: 1, 
        overflow: TextOverflow.ellipsis,
        style: textRegular.copyWith(fontSize: Dimensions.fontSizeDefault)
      ),
    );
  }
}
