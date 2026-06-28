import 'package:country_code_picker/country_code_picker.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_sixvalley_ecommerce/common/basewidget/custom_asset_image_widget.dart';
import 'package:flutter_sixvalley_ecommerce/helper/responsive_helper.dart';
import 'package:flutter_sixvalley_ecommerce/theme/controllers/theme_controller.dart';
import 'package:flutter_sixvalley_ecommerce/utill/custom_themes.dart';
import 'package:flutter_sixvalley_ecommerce/utill/dimensions.dart';
import 'package:flutter_sixvalley_ecommerce/features/auth/widgets/code_picker_widget.dart';
import 'package:provider/provider.dart';


class CustomTextFieldWidget extends StatefulWidget {
  final String? hintText;
  final String? titleText;
  final String? labelText;
  final TextAlign textAlign;
  final TextEditingController? controller;
  final FocusNode? focusNode;
  final FocusNode? nextFocus;
  final TextInputType inputType;
  final TextInputAction inputAction;
  final bool isPassword;
  final bool isAmount;
  final bool showCodePicker;
  final bool isRequiredFill;
  final bool readOnly;
  final bool filled;
  final void Function()? onTap;
  final void Function()? suffixOnTap;
  final void Function()? suffix2OnTap;
  final void Function()? prefixOnTap;
  final void Function(String value)? onChanged;
  final String? Function(String?)? validator;
  final bool isEnabled;
  final int maxLines;
  final TextCapitalization capitalization;
  final double borderRadius;
  final String? prefixIcon;
  final String? suffixIcon;
  final String? suffixIcon2;
  final double suffixIconSize;
  final bool showBorder;
  final bool showLabelText;
  final String? countryDialCode;
  final double prefixHeight;
  final Color borderColor;
  final List<TextInputFormatter>? inputFormatters;
  final Function(CountryCode countryCode)? onCountryChanged;
  final bool required;
  final Color? prefixColor;
  final Color? suffixColor;
  final bool isShowBorder;
  final bool isToolTipSuffix;
  final String? toolTipMessage;
  final GlobalKey? toolTipKey;
  final TextStyle? labelTextStyle;
  final EdgeInsetsGeometry? padding;
  final bool? isDense;

  const CustomTextFieldWidget({
    super.key,
    this.hintText = 'Write something...',
    this.controller,
    this.focusNode,
    this.titleText,
    this.nextFocus,
    this.isEnabled = true,
    this.borderColor = const Color(0xFFE0E0E0), 
    this.inputType = TextInputType.text,
    this.inputAction = TextInputAction.next,
    this.maxLines = 1,
    this.onChanged,
    this.onTap,
    this.prefixIcon,
    this.suffixIcon,
    this.suffixIconSize= 12,
    this.capitalization = TextCapitalization.none,
    this.readOnly = false,
    this.isPassword = false,
    this.isAmount = false,
    this.showCodePicker = false,
    this.isRequiredFill = false,
    this.showLabelText = true,
    this.showBorder = true,
    this.filled = true,
    this.borderRadius = 12, 
    this.prefixHeight = 50,
    this.countryDialCode,
    this.onCountryChanged,
    this.validator,
    this.inputFormatters,
    this.labelText,
    this.textAlign = TextAlign.start,
     this.required = false, this.suffixOnTap, this.suffix2OnTap, this.prefixOnTap,
    this.prefixColor,
    this.suffixColor,
    this.suffixIcon2,
    this.isShowBorder = false,
    this.isToolTipSuffix = false,
    this.toolTipMessage,
    this.toolTipKey,
    this.labelTextStyle,
    this.padding,
    this.isDense
  });

  @override
  State<CustomTextFieldWidget> createState() => _CustomTextFieldWidgetState();
}

class _CustomTextFieldWidgetState extends State<CustomTextFieldWidget> {
  bool _obscureText = true;
  bool isFocusActive = false;

  @override
  void initState() {
    widget.focusNode?.addListener(() {
      if (mounted) {
        setState(() {
          isFocusActive = widget.focusNode!.hasFocus;
        });
      }
    });
    super.initState();
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        if (widget.titleText != null)
          Padding(
            padding: const EdgeInsets.only(bottom: 8.0, left: 4.0),
            child: Text(
              widget.titleText!,
              style: textBold.copyWith(
                fontSize: 14, 
                color: const Color(0xFF222222), 
              ),
            ),
          ),
        
        TextFormField(
          maxLines: widget.maxLines,
          controller: widget.controller,
          focusNode: widget.focusNode,
          validator: widget.validator,
          textAlign: widget.textAlign,
          readOnly: widget.readOnly,
          onTap: widget.onTap,
          autovalidateMode: AutovalidateMode.onUserInteraction,
          style: textRegular.copyWith(
            fontSize: 14, 
            color: const Color(0xFF222222), 
            fontWeight: FontWeight.w500
          ),
          textInputAction: widget.inputAction,
          keyboardType: widget.inputType,
          cursorColor: theme.primaryColor,
          enabled: widget.isEnabled,
          obscureText: widget.isPassword ? _obscureText : false,
          decoration: InputDecoration(
            isDense: widget.isDense,
            fillColor: Colors.white,
            filled: true,
            contentPadding: widget.padding ?? const EdgeInsets.symmetric(horizontal: 16, vertical: 15),
            
            hintText: widget.hintText,
            hintStyle: textRegular.copyWith(
              fontSize: 13, 
              color: const Color(0xFF777777).withOpacity(0.8),
            ),

            labelText: widget.showLabelText ? widget.labelText : null,
            labelStyle: textRegular.copyWith(
              color: isFocusActive ? theme.primaryColor : const Color(0xFF777777),
              fontSize: 14,
            ),

            enabledBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(widget.borderRadius),
              borderSide: const BorderSide(color: Color(0xFFF1F1F1), width: 1.0),
            ),

            focusedBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(widget.borderRadius),
              borderSide: BorderSide(color: theme.primaryColor, width: 1.5),
            ),

            errorBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(widget.borderRadius),
              borderSide: BorderSide(color: theme.colorScheme.error.withOpacity(0.5), width: 1.0),
            ),

            prefixIcon: widget.prefixIcon != null ? Padding(
              padding: const EdgeInsets.all(12.0),
              child: CustomAssetImageWidget(
                widget.prefixIcon!, 
                height: 20, width: 20, 
                color: isFocusActive ? theme.primaryColor : const Color(0xFF777777)
              ),
            ) : widget.showCodePicker ? _buildCodePicker(theme) : null,

            suffixIcon: widget.isPassword ? IconButton(
              icon: Icon(_obscureText ? Icons.visibility_off_outlined : Icons.visibility_outlined, 
                color: const Color(0xFF777777), size: 20),
              onPressed: _toggle,
            ) : widget.suffixIcon != null ? InkWell(
              onTap: widget.suffixOnTap,
              child: Padding(
                padding: const EdgeInsets.all(12.0),
                child: Image.asset(widget.suffixIcon!, color: const Color(0xFF777777), width: 20, height: 20),
              ),
            ) : null,
          ),
          onFieldSubmitted: (text) => widget.nextFocus != null ? FocusScope.of(context).requestFocus(widget.nextFocus) : null,
          onChanged: widget.onChanged,
        ),
      ],
    );
  }

  Widget _buildCodePicker(ThemeData theme) {
    return SizedBox(
      width: ResponsiveHelper.isTab(context) ? 130 : 110, // تم زيادة العرض لضمان ظهور العلم ومفتاح الدولة بوضوح
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
        Expanded(
          child: CodePickerWidget(
            padding: const EdgeInsets.all(0),
            onChanged: widget.onCountryChanged,
            initialSelection: widget.countryDialCode,
            showDropDownButton: true,
            showCountryOnly: false,
            textStyle: textRegular.copyWith(fontSize: 14, color: const Color(0xFF222222)),
          ),
        ),
        Container(height: 20, width: 1, color: const Color(0xFFF1F1F1), margin: const EdgeInsets.symmetric(horizontal: 2)),
      ]),
    );
  }

  void _toggle() {
    setState(() {
      _obscureText = !_obscureText;
    });
  }
}
