import 'package:flutter/material.dart';
import 'package:flutter_sixvalley_ecommerce/features/address/controllers/address_controller.dart';
import 'package:flutter_sixvalley_ecommerce/features/address/domain/models/address_model.dart';
import 'package:flutter_sixvalley_ecommerce/features/address/widgets/address_form_widget.dart';
import 'package:flutter_sixvalley_ecommerce/features/auth/controllers/auth_controller.dart';
import 'package:flutter_sixvalley_ecommerce/features/checkout/controllers/checkout_controller.dart';
import 'package:flutter_sixvalley_ecommerce/localization/language_constrants.dart';
import 'package:flutter_sixvalley_ecommerce/utill/custom_themes.dart';
import 'package:flutter_sixvalley_ecommerce/utill/dimensions.dart';
import 'package:provider/provider.dart';

class ShippingDetailsWidget extends StatefulWidget {
  final bool hasPhysical;
  final bool billingAddress;
  final GlobalKey<FormState> passwordFormKey;

  const ShippingDetailsWidget({super.key, required this.hasPhysical, required this.billingAddress, required this.passwordFormKey});

  @override
  State<ShippingDetailsWidget> createState() => _ShippingDetailsWidgetState();
}

class _ShippingDetailsWidgetState extends State<ShippingDetailsWidget> {
  
  @override
  void initState() {
    super.initState();
    _loadInitialData();
  }

  void _loadInitialData() {
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final addressController = Provider.of<AddressController>(context, listen: false);
      final checkoutController = Provider.of<CheckoutController>(context, listen: false);
      
      addressController.getAddressList().then((list) {
        if (list != null && list.isNotEmpty) {
          if (checkoutController.addressIndex == null) {
            checkoutController.setAddressIndex(0);
            checkoutController.setBillingAddressIndex(0);
          }
        }
      });
      
      if (!checkoutController.sameAsBilling) {
        checkoutController.setSameAsBilling(isUpdate: true);
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return Consumer<CheckoutController>(
      builder: (context, checkoutProvider, _) {
        return Consumer<AddressController>(
          builder: (context, locationProvider, _) {
            final addressList = locationProvider.addressList;
            bool hasAddresses = addressList != null && addressList.isNotEmpty;

            return Container(
              width: double.infinity,
              padding: const EdgeInsets.symmetric(vertical: Dimensions.paddingSizeSmall),
              decoration: BoxDecoration(
                color: Theme.of(context).cardColor,
                boxShadow: [BoxShadow(color: Theme.of(context).hintColor.withOpacity(0.02), blurRadius: 10, offset: const Offset(0, 5))],
              ),
              child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: Dimensions.paddingSizeDefault),
                  child: Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
                    Expanded(child: Row(children: [
                      Icon(Icons.location_on_rounded, color: Theme.of(context).primaryColor, size: 20),
                      const SizedBox(width: 8),
                      Expanded(child: Text(getTranslated('shipping_address', context)!, 
                        style: textBold.copyWith(fontSize: Dimensions.fontSizeDefault),
                        maxLines: 1, overflow: TextOverflow.ellipsis)),
                    ])),
                    TextButton(
                      onPressed: () => _showAddAddressModal(context),
                      style: TextButton.styleFrom(padding: EdgeInsets.zero, minimumSize: const Size(50, 30)),
                      child: Text(getTranslated('add_new', context)!, 
                        style: textBold.copyWith(color: Theme.of(context).primaryColor, fontSize: 12)),
                    ),
                  ]),
                ),

                if (hasAddresses)
                  SizedBox(
                    height: 125,
                    child: ListView.builder(
                      scrollDirection: Axis.horizontal,
                      padding: const EdgeInsets.symmetric(horizontal: Dimensions.paddingSizeDefault),
                      itemCount: addressList.length,
                      itemBuilder: (context, index) {
                        bool isSelected = checkoutProvider.addressIndex == index;
                        final address = addressList[index];
                        String arabicType = address.addressType?.toLowerCase() == 'home' ? 'منزل' : 
                                           address.addressType?.toLowerCase() == 'office' ? 'مكتب' : 'أخرى';

                        return Padding(
                          padding: const EdgeInsets.only(right: 12, bottom: 8, top: 4),
                          child: Stack(children: [
                            InkWell(
                              onTap: () {
                                checkoutProvider.setAddressIndex(index);
                                checkoutProvider.setBillingAddressIndex(index);
                              },
                              borderRadius: BorderRadius.circular(12),
                              child: Container(
                                width: MediaQuery.of(context).size.width * 0.70,
                                padding: const EdgeInsets.all(12),
                                decoration: BoxDecoration(
                                  color: isSelected ? Theme.of(context).primaryColor.withOpacity(0.03) : Theme.of(context).cardColor,
                                  borderRadius: BorderRadius.circular(12),
                                  border: Border.all(
                                    color: isSelected ? Theme.of(context).primaryColor : Theme.of(context).hintColor.withOpacity(0.15),
                                    width: isSelected ? 1.5 : 1,
                                  ),
                                  boxShadow: isSelected ? [BoxShadow(color: Theme.of(context).primaryColor.withOpacity(0.1), blurRadius: 8)] : null,
                                ),
                                child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                                  Row(children: [
                                    Icon(isSelected ? Icons.check_circle : Icons.circle_outlined, 
                                         color: isSelected ? Theme.of(context).primaryColor : Theme.of(context).hintColor, size: 16),
                                    const SizedBox(width: 8),
                                    Text(arabicType, 
                                      style: textBold.copyWith(fontSize: 13, color: isSelected ? Theme.of(context).primaryColor : null)),
                                  ]),
                                  const Spacer(),
                                  Text(address.contactPersonName ?? '', style: textMedium.copyWith(fontSize: 12), maxLines: 1, overflow: TextOverflow.ellipsis),
                                  Text(address.phone ?? '', style: textRegular.copyWith(fontSize: 11, color: Theme.of(context).hintColor)),
                                  Text(address.address ?? '', 
                                    style: textRegular.copyWith(fontSize: 10, color: Theme.of(context).hintColor.withOpacity(0.7)), 
                                    maxLines: 1, overflow: TextOverflow.ellipsis),
                                ]),
                              ),
                            ),

                            Positioned(
                              top: 8, left: 8,
                              child: Row(children: [
                                _actionBtn(context, Icons.edit, Colors.blue, () => _showAddAddressModal(context, address: address)),
                                const SizedBox(width: 8),
                                _actionBtn(context, Icons.delete_outline, Colors.red, () {
                                  showDialog(context: context, builder: (ctx) => AlertDialog(
                                    title: Text(getTranslated('delete_address', context)!),
                                    content: Text(getTranslated('are_you_sure_want_to_delete_this_address', context)!),
                                    actions: [
                                      TextButton(onPressed: () => Navigator.pop(context), child: Text(getTranslated('cancel', context)!)),
                                      TextButton(onPressed: () {
                                        locationProvider.deleteAddress(address.id!);
                                        Navigator.pop(context);
                                      }, child: Text(getTranslated('delete', context)!, style: const TextStyle(color: Colors.red))),
                                    ],
                                  ));
                                }),
                              ]),
                            ),
                          ]),
                        );
                      },
                    ),
                  )
                else
                  _buildEmptyState(context),
              ]),
            );
          },
        );
      },
    );
  }

  Widget _actionBtn(BuildContext context, IconData icon, Color color, VoidCallback onTap) {
    return InkWell(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.all(4),
        decoration: BoxDecoration(color: color.withOpacity(0.08), shape: BoxShape.circle),
        child: Icon(icon, color: color, size: 14),
      ),
    );
  }

  void _showAddAddressModal(BuildContext context, {AddressModel? address}) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) => Container(
        decoration: BoxDecoration(
          color: Theme.of(context).scaffoldBackgroundColor,
          borderRadius: const BorderRadius.vertical(top: Radius.circular(20)),
        ),
        padding: EdgeInsets.only(
          top: 15, left: 15, right: 15,
          bottom: MediaQuery.of(context).viewInsets.bottom + 15,
        ),
        child: Column(mainAxisSize: MainAxisSize.min, children: [
          Container(width: 35, height: 4, decoration: BoxDecoration(color: Theme.of(context).hintColor.withOpacity(0.2), borderRadius: BorderRadius.circular(10))),
          const SizedBox(height: 15),
          Text(getTranslated(address == null ? 'add_new_address' : 'update_address', context)!, style: textBold),
          const SizedBox(height: 15),
          Flexible(child: SingleChildScrollView(child: AddressFormWidget(fromCheckout: true, address: address, isEnableUpdate: address != null, onSuccess: () => Navigator.pop(context)))),
        ]),
      ),
    );
  }

  Widget _buildEmptyState(BuildContext context) {
    return InkWell(
      onTap: () => _showAddAddressModal(context),
      child: Container(
        margin: const EdgeInsets.all(Dimensions.paddingSizeDefault),
        padding: const EdgeInsets.symmetric(vertical: 25),
        width: double.infinity,
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: Theme.of(context).primaryColor.withOpacity(0.15), style: BorderStyle.solid),
          color: Theme.of(context).primaryColor.withOpacity(0.01),
        ),
        child: Column(children: [
          Icon(Icons.add_location_alt_outlined, size: 35, color: Theme.of(context).primaryColor.withOpacity(0.3)),
          const SizedBox(height: 10),
          Text(getTranslated('no_address_found', context)!, style: textMedium.copyWith(color: Theme.of(context).hintColor, fontSize: 13)),
        ]),
      ),
    );
  }
}
