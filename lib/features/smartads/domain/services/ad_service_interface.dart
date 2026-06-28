import 'package:flutter_sixvalley_ecommerce/data/model/api_response.dart';

abstract class AdServiceInterface {
  Future<ApiResponseModel> saveFcmToken(String token, int? userId, String deviceType, String browser);
  Future<ApiResponseModel> getActiveAds(String device, String region, int? categoryId, int? userId);
  Future<ApiResponseModel> trackImpression(int adId);
  Future<ApiResponseModel> trackClick(int adId);
  Future<ApiResponseModel> getPendingInAppBanners(int? userId, String device);
  Future<ApiResponseModel> getNotifications(int? userId);
  Future<ApiResponseModel> markNotificationAsRead(int notificationId);
}
