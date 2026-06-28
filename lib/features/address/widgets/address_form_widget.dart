import 'package:country_code_picker/country_code_picker.dart';
import 'package:flutter/material.dart';
import 'package:flutter_sixvalley_ecommerce/features/address/controllers/address_controller.dart';
import 'package:flutter_sixvalley_ecommerce/features/address/domain/models/address_model.dart';
import 'package:flutter_sixvalley_ecommerce/features/auth/controllers/auth_controller.dart';
import 'package:flutter_sixvalley_ecommerce/features/checkout/controllers/checkout_controller.dart';
import 'package:flutter_sixvalley_ecommerce/features/location/controllers/location_controller.dart';
import 'package:flutter_sixvalley_ecommerce/features/location/screens/select_location_screen.dart';
import 'package:flutter_sixvalley_ecommerce/helper/country_code_helper.dart';
import 'package:flutter_sixvalley_ecommerce/helper/velidate_check.dart';
import 'package:flutter_sixvalley_ecommerce/localization/language_constrants.dart';
import 'package:flutter_sixvalley_ecommerce/main.dart';
import 'package:flutter_sixvalley_ecommerce/utill/custom_themes.dart';
import 'package:flutter_sixvalley_ecommerce/utill/dimensions.dart';
import 'package:flutter_sixvalley_ecommerce/utill/images.dart';
import 'package:flutter_sixvalley_ecommerce/common/basewidget/custom_button_widget.dart';
import 'package:flutter_sixvalley_ecommerce/common/basewidget/custom_textfield_widget.dart';
import 'package:provider/provider.dart';

class AddressFormWidget extends StatefulWidget {
  final bool isEnableUpdate;
  final bool fromCheckout;
  final AddressModel? address;
  final bool? isBilling;
  final Function? onSuccess;

  const AddressFormWidget({
    super.key,
    this.isEnableUpdate = false,
    this.address,
    this.fromCheckout = false,
    this.isBilling = false,
    this.onSuccess,
  });

  @override
  State<AddressFormWidget> createState() => _AddressFormWidgetState();
}

class _AddressFormWidgetState extends State<AddressFormWidget> {
  final TextEditingController _contactPersonNameController = TextEditingController();
  final TextEditingController _contactPersonNumberController = TextEditingController();
  final TextEditingController _cityController = TextEditingController();
  final TextEditingController _zipController = TextEditingController(text: '0000');
  final TextEditingController _countryCodeController = TextEditingController(text: 'Yemen');
  final FocusNode _addressNode = FocusNode();
  final FocusNode _nameNode = FocusNode();
  final FocusNode _numberNode = FocusNode();
  final FocusNode _cityNode = FocusNode();

  final GlobalKey<FormState> _addressFormKey = GlobalKey();

  @override
  void initState() {
    super.initState();
    Provider.of<AuthController>(context, listen: false).setCountryCode('+967', notify: false);
    Provider.of<AddressController>(context, listen: false).getAddressType();

    if (widget.isEnableUpdate && widget.address != null) {
      _contactPersonNameController.text = '${widget.address?.contactPersonName}';
      _cityController.text = '${widget.address?.city}';
      _zipController.text = widget.address?.zip ?? '0000';
      _contactPersonNumberController.text = CountryCodeHelper.extractPhoneNumber(
          CountryCodeHelper.getCountryCode(widget.address?.phone ?? '') ?? '+967', widget.address?.phone ?? '');
      Provider.of<LocationController>(context, listen: false).locationController.text = widget.address?.address ?? '';
    }
  }

  @override
  Widget build(BuildContext context) {
    return Consumer<AddressController>(builder: (context, addressController, child) {
      return Consumer<LocationController>(builder: (context, locationController, _) {
        
        if(locationController.address.administrativeArea != null && locationController.address.administrativeArea!.isNotEmpty) {
           _cityController.text = locationController.address.administrativeArea!;
        }

        return Form(
          key: _addressFormKey,
          child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [

            _buildFieldLabel(getTranslated('contact_person_name', context)!),
            _buildInputWrapper(
              child: CustomTextFieldWidget(
                required: true,
                prefixIcon: Images.user,
                hintText: getTranslated('enter_contact_person_name', context),
                inputType: TextInputType.name,
                controller: _contactPersonNameController,
                focusNode: _nameNode,
                nextFocus: _numberNode,
                inputAction: TextInputAction.next,
                validator: (value) => ValidateCheck.validateEmptyText(value, 'contact_person_name_is_required'),
              ),
            ),

            const SizedBox(height: Dimensions.paddingSizeDefault),

            _buildFieldLabel(getTranslated('phone', context)!),
            Consumer<AuthController>(builder: (context, authProvider, _) {
              return Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
                Container(
                  height: 50, width: 115,
                  decoration: BoxDecoration(
                    color: Theme.of(context).cardColor,
                    borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
                    border: Border.all(color: Theme.of(context).primaryColor.withOpacity(0.4), width: 1.2),
                  ),
                  child: CountryCodePicker(
                    onChanged: (CountryCode countryCode) {
                      authProvider.countryDialCode = countryCode.dialCode!;
                      authProvider.setCountryCode(countryCode.dialCode!);
                    },
                    initialSelection: authProvider.countryDialCode,
                    favorite: const ['YE', '+967'],
                    showDropDownButton: true,
                    padding: EdgeInsets.zero,
                    showOnlyCountryWhenClosed: false,
                    alignLeft: false,
                    flagWidth: 30,
                    textStyle: textBold.copyWith(
                      fontSize: Dimensions.fontSizeDefault,
                      color: Theme.of(context).textTheme.bodyLarge?.color,
                    ),
                  ),
                ),
                const SizedBox(width: 10),

                Expanded(
                  child: _buildInputWrapper(
                    child: CustomTextFieldWidget(
                      required: true,
                      hintText: '7xxxxxxxx',
                      controller: _contactPersonNumberController,
                      focusNode: _numberNode,
                      nextFocus: _addressNode,
                      inputType: TextInputType.phone,
                      isAmount: true,
                      validator: (value) => ValidateCheck.validateEmptyText(value, "phone_must_be_required"),
                    ),
                  ),
                ),
              ]);
            }),

            const SizedBox(height: Dimensions.paddingSizeDefault),

            Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
              _buildFieldLabel(getTranslated('delivery_address', context)!),
              TextButton.icon(
                onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const SelectLocationScreen(googleMapController: null))),
                icon: Icon(Icons.map_outlined, size: 18, color: Theme.of(context).primaryColor),
                label: Text("تحديد من الخريطة", style: textBold.copyWith(color: Theme.of(context).primaryColor, fontSize: Dimensions.fontSizeSmall)),
              ),
            ]),
            _buildInputWrapper(
              child: CustomTextFieldWidget(
                hintText: getTranslated('delivery_address', context),
                inputType: TextInputType.streetAddress,
                focusNode: _addressNode,
                nextFocus: _cityNode,
                prefixIcon: Images.address,
                required: true,
                controller: locationController.locationController,
                validator: (value) => ValidateCheck.validateEmptyText(value, "address_is_required"),
              ),
            ),

            const SizedBox(height: Dimensions.paddingSizeDefault),

            _buildFieldLabel("المحافظة / المدينة"),
            _buildInputWrapper(
              child: CustomTextFieldWidget(
                hintText: 'أدخل اسم المحافظة',
                inputType: TextInputType.text,
                focusNode: _cityNode,
                required: true,
                prefixIcon: Images.city,
                controller: _cityController,
                validator: (value) => ValidateCheck.validateEmptyText(value, 'city_is_required'),
              ),
            ),

            const SizedBox(height: Dimensions.paddingSizeDefault),

            _buildFieldLabel("نوع العنوان"),
            SizedBox(
              height: 45,
              child: ListView.builder(
                shrinkWrap: true,
                scrollDirection: Axis.horizontal,
                itemCount: addressController.addressTypeList.length,
                itemBuilder: (context, index) {
                  String typeTitle = addressController.addressTypeList[index].title;
                  String arabicTitle = typeTitle.toLowerCase() == 'home' ? 'منزل' : 
                                      typeTitle.toLowerCase() == 'office' ? 'مكتب' : 'أخرى';
                  
                  return InkWell(
                    onTap: () => addressController.updateAddressIndex(index, true),
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: Dimensions.paddingSizeDefault),
                      margin: const EdgeInsets.only(right: Dimensions.paddingSizeSmall),
                      decoration: BoxDecoration(
                        borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
                        color: addressController.selectAddressIndex == index 
                            ? Theme.of(context).primaryColor 
                            : Theme.of(context).cardColor,
                        border: Border.all(color: Theme.of(context).primaryColor.withOpacity(0.2)),
                      ),
                      child: Center(
                        child: Text(arabicTitle,
                          style: textMedium.copyWith(
                            color: addressController.selectAddressIndex == index ? Colors.white : Theme.of(context).hintColor,
                          ),
                        ),
                      ),
                    ),
                  );
                },
              ),
            ),

            const SizedBox(height: 30),

            SizedBox(
              height: 50,
              child: CustomButton(
                isLoading: addressController.isLoading,
                buttonText: widget.isEnableUpdate ? getTranslated('update_address', context) : getTranslated('save_location', context),
                onTap: () {
                  if (_addressFormKey.currentState?.validate() ?? false) {
                    final authProvider = Provider.of<AuthController>(context, listen: false);
                    
                    AddressModel addressModel = AddressModel(
                      addressType: addressController.addressTypeList.isNotEmpty ? addressController.addressTypeList[addressController.selectAddressIndex].title : 'Home',
                      contactPersonName: _contactPersonNameController.text.trim(),
                      phone: '${authProvider.countryDialCode}${_contactPersonNumberController.text.trim()}',
                      city: _cityController.text.trim(),
                      zip: _zipController.text.trim(), 
                      address: locationController.locationController.text.trim(),
                      country: _countryCodeController.text,
                      isBilling: widget.isBilling ?? true,
                      latitude: "المحافظة: ${_cityController.text.trim()}",
                      longitude: "العنوان: ${locationController.locationController.text.trim()}",
                      isGuest: !authProvider.isLoggedIn(),
                      guestId: authProvider.getGuestToken(),
                    );

                    if (widget.isEnableUpdate) {
                      addressModel.id = widget.address!.id;
                      addressController.updateAddress(context, addressModel: addressModel, addressId: addressModel.id).then((value) {
                        Navigator.pop(context);
                      });
                    } else {
                      addressController.addAddress(addressModel).then((value) {
                        if (value.response?.statusCode == 200) {
                          addressController.getAddressList().then((list) {
                            if (widget.fromCheckout && list != null && list.isNotEmpty) {
                              Provider.of<CheckoutController>(context, listen: false).setAddressIndex(0);
                            }
                            if (widget.onSuccess != null) {
                              widget.onSuccess!();
                            } else {
                              Navigator.pop(context);
                            }
                          });
                        }
                      });
                    }
                  }
                },
              ),
            ),
            const SizedBox(height: 20),
          ]),
        );
      });
    });
  }

  Widget _buildInputWrapper({required Widget child}) {
    return Container(
      decoration: BoxDecoration(
        color: Theme.of(context).cardColor,
        borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
        border: Border.all(color: Theme.of(context).primaryColor.withOpacity(0.1), width: 1),
        boxShadow: [
          BoxShadow(
              color: Colors.black.withOpacity(0.02),
              spreadRadius: 1,
              blurRadius: 4,
              offset: const Offset(0, 2)
          )
        ],
      ),
      child: child,
    );
  }

  Widget _buildFieldLabel(String label) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 6, top: 10, right: 5),
      child: Text(
        label,
        style: textBold.copyWith(
          fontSize: Dimensions.fontSizeDefault,
          color: Theme.of(context).primaryColor.withOpacity(0.8),
        ),
      ),
    );
  }
}
