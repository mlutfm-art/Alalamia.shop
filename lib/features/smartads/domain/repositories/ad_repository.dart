import 'package:dio/dio.dart';
import 'package:flutter_sixvalley_ecommerce/data/datasource/remote/dio/dio_client.dart';
import 'package:flutter_sixvalley_ecommerce/data/datasource/remote/exception/api_error_handler.dart';
import 'package:flutter_sixvalley_ecommerce/data/model/api_response.dart';
import 'package:flutter_sixvalley_ecommerce/features/smartads/domain/repositories/ad_repository_interface.dart';
import 'package:flutter_sixvalley_ecommerce/utill/app_constants.dart';

class AdRepository implements AdRepositoryInterface {
  final DioClient dioClient;
  AdRepository({required this.dioClient});

  @override
  Future<ApiResponseModel> saveFcmToken(String token, int? userId, String deviceType, String browser) async {
    try {
      Response response = await dioClient.post(
        AppConstants.smartAdsFcmTokenSaveUri,
        data: {
          "token": token,
          if (userId != null && userId > 0) "user_id": userId,
          "device_type": deviceType,
          "browser": browser,
        },
      );
      return ApiResponseModel.withSuccess(response);
    } catch (e) {
      return ApiResponseModel.withError(ApiErrorHandler.getMessage(e));
    }
  }

  @override
  Future<ApiResponseModel> getActiveAds(String device, String region, int? categoryId, int? userId) async {
    try {
      Map<String, dynamic> queryParameters = {
        "device": device,
        "region": region,
      };
      if (categoryId != null) queryParameters["category_id"] = categoryId;
      if (userId != null && userId > 0) queryParameters["user_id"] = userId;

      Response response = await dioClient.get(
        AppConstants.smartAdsGetActiveUri,
        queryParameters: queryParameters,
      );
      return ApiResponseModel.withSuccess(response);
    } catch (e) {
      return ApiResponseModel.withError(ApiErrorHandler.getMessage(e));
    }
  }

  @override
  Future<ApiResponseModel> trackImpression(int adId) async {
    try {
      Response response = await dioClient.post(
        '${AppConstants.smartAdsTrackImpressionUri}/$adId',
      );
      return ApiResponseModel.withSuccess(response);
    } catch (e) {
      return ApiResponseModel.withError(ApiErrorHandler.getMessage(e));
    }
  }

  @override
  Future<ApiResponseModel> trackClick(int adId) async {
    try {
      Response response = await dioClient.post(
        '${AppConstants.smartAdsTrackClickUri}/$adId',
      );
      return ApiResponseModel.withSuccess(response);
    } catch (e) {
      return ApiResponseModel.withError(ApiErrorHandler.getMessage(e));
    }
  }

  @override
  Future<ApiResponseModel> getPendingInAppBanners(int? userId, String device) async {
    try {
      Map<String, dynamic> queryParameters = {
        "device": device,
      };
      if (userId != null && userId > 0) queryParameters["user_id"] = userId;

      Response response = await dioClient.get(
        AppConstants.smartAdsPendingBannersUri,
        queryParameters: queryParameters,
      );
      return ApiResponseModel.withSuccess(response);
    } catch (e) {
      return ApiResponseModel.withError(ApiErrorHandler.getMessage(e));
    }
  }

  @override
  Future<ApiResponseModel> getNotifications(int? userId) async {
    try {
      Map<String, dynamic> queryParameters = {};
      if (userId != null && userId > 0) queryParameters["user_id"] = userId;

      Response response = await dioClient.get(
        AppConstants.smartAdsNotificationsUri,
        queryParameters: queryParameters,
      );
      return ApiResponseModel.withSuccess(response);
    } catch (e) {
      return ApiResponseModel.withError(ApiErrorHandler.getMessage(e));
    }
  }

  @override
  Future<ApiResponseModel> markNotificationAsRead(int notificationId) async {
    try {
      Response response = await dioClient.post(
        '${AppConstants.smartAdsNotificationsUri}/$notificationId/read',
      );
      return ApiResponseModel.withSuccess(response);
    } catch (e) {
      return ApiResponseModel.withError(ApiErrorHandler.getMessage(e));
    }
  }
}
