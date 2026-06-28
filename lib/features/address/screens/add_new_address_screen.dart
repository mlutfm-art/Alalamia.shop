import 'package:flutter/material.dart';
import 'package:flutter_sixvalley_ecommerce/features/address/domain/models/address_model.dart';
import 'package:flutter_sixvalley_ecommerce/features/address/widgets/address_form_widget.dart';
import 'package:flutter_sixvalley_ecommerce/localization/language_constrants.dart';
import 'package:flutter_sixvalley_ecommerce/common/basewidget/custom_app_bar_widget.dart';

class AddNewAddressScreen extends StatelessWidget {
  final bool isEnableUpdate;
  final bool fromCheckout;
  final AddressModel? address;
  final bool? isBilling;
  const AddNewAddressScreen({super.key, this.isEnableUpdate = false, this.address, this.fromCheckout = false, this.isBilling});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: CustomAppBar(title: isEnableUpdate ? getTranslated('update_address', context) : getTranslated('add_new_address', context)),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16.0),
        child: AddressFormWidget(
          isEnableUpdate: isEnableUpdate,
          fromCheckout: fromCheckout,
          address: address,
          isBilling: isBilling,
        ),
      ),
    );
  }
}
