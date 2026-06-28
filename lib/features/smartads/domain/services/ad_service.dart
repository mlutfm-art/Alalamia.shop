import 'package:flutter_sixvalley_ecommerce/data/model/api_response.dart';
import 'package:flutter_sixvalley_ecommerce/features/smartads/domain/repositories/ad_repository_interface.dart';
import 'package:flutter_sixvalley_ecommerce/features/smartads/domain/services/ad_service_interface.dart';

class AdService implements AdServiceInterface {
  final AdRepositoryInterface adRepository;
  AdService({required this.adRepository});

  @override
  Future<ApiResponseModel> saveFcmToken(String token, int? userId, String deviceType, String browser) {
    return adRepository.saveFcmToken(token, userId, deviceType, browser);
  }

  @override
  Future<ApiResponseModel> getActiveAds(String device, String region, int? categoryId, int? userId) {
    return adRepository.getActiveAds(device, region, categoryId, userId);
  }

  @override
  Future<ApiResponseModel> trackImpression(int adId) {
    return adRepository.trackImpression(adId);
  }

  @override
  Future<ApiResponseModel> trackClick(int adId) {
    return adRepository.trackClick(adId);
  }

  @override
  Future<ApiResponseModel> getPendingInAppBanners(int? userId, String device) {
    return adRepository.getPendingInAppBanners(userId, device);
  }

  @override
  Future<ApiResponseModel> getNotifications(int? userId) {
    return adRepository.getNotifications(userId);
  }

  @override
  Future<ApiResponseModel> markNotificationAsRead(int notificationId) {
    return adRepository.markNotificationAsRead(notificationId);
  }
}
