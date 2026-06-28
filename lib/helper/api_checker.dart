import 'dart:developer';
import 'package:flutter_sixvalley_ecommerce/data/model/api_response.dart';
import 'package:flutter_sixvalley_ecommerce/data/model/error_response.dart';
import 'package:flutter_sixvalley_ecommerce/localization/language_constrants.dart';
import 'package:flutter_sixvalley_ecommerce/main.dart';
import 'package:flutter_sixvalley_ecommerce/features/auth/controllers/auth_controller.dart';
import 'package:flutter_sixvalley_ecommerce/common/basewidget/show_custom_snakbar_widget.dart';
import 'package:provider/provider.dart';

class ApiChecker {
  static void checkApi(ApiResponseModel apiResponse, {bool firebaseResponse = false}) {

    if(apiResponse.error == "Failed to load data - status code: 401") {
      Provider.of<AuthController>(Get.context!,listen: false).clearSharedData();
    } else if(apiResponse.response?.statusCode == 500) {
        showCustomSnackBarWidget(getTranslated('internal_server_error', Get.context!),  Get.context!,  snackBarType: SnackBarType.error);
    } else if(apiResponse.response?.statusCode == 503) {
        showCustomSnackBarWidget(apiResponse.response?.data['message'],  Get.context!,  snackBarType: SnackBarType.error);
    } else {
      String? errorMessage;
      if (apiResponse.error is String) {
        errorMessage = apiResponse.error.toString();
      } else if (apiResponse.error != null) {
        try {
          ErrorResponse errorResponse = ErrorResponse.fromJson(apiResponse.error);
          errorMessage = errorResponse.errors?[0].message;
        } catch (e) {
          errorMessage = apiResponse.error.toString();
        }
      } else if (apiResponse.response?.data != null && apiResponse.response?.data is Map) {
        // إذا كان الخطأ موجوداً في الـ data (مثل رسائل الباك اند المخصصة)
        errorMessage = apiResponse.response?.data['error'] ?? apiResponse.response?.data['message'];
      }

      errorMessage ??= getTranslated('something_went_wrong', Get.context!);
      
      showCustomSnackBarWidget(firebaseResponse ? errorMessage?.replaceAll('_', ' ') : errorMessage,  Get.context!,  snackBarType: SnackBarType.error);
    }
  }

  static ErrorResponse getError(ApiResponseModel apiResponse){
    ErrorResponse error;
    try{
      error = ErrorResponse.fromJson(apiResponse.response?.data);
    }catch(e){
      if(apiResponse.error is String){
        error = ErrorResponse(errors: [Errors(code: '', message: apiResponse.error.toString())]);
      }else if (apiResponse.error != null){
        error = ErrorResponse.fromJson(apiResponse.error);
      } else {
        error = ErrorResponse(errors: [Errors(code: '', message: 'Unknown Error')]);
      }
    }
    return error;
  }
}
