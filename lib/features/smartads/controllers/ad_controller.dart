import 'package:flutter/material.dart';
import 'package:flutter_sixvalley_ecommerce/data/model/api_response.dart';
import 'package:flutter_sixvalley_ecommerce/features/smartads/domain/models/smart_ad_model.dart';
import 'package:flutter_sixvalley_ecommerce/features/smartads/domain/services/ad_service_interface.dart';
import 'package:flutter_sixvalley_ecommerce/helper/api_checker.dart';

class AdController extends ChangeNotifier {
  final AdServiceInterface adService;
  AdController({required this.adService});

  List<SmartAdModel> _activeAds = [];
  List<SmartAdModel> _pendingInAppBanners = [];
  List<SmartAdNotificationModel> _notifications = [];
  bool _isLoading = false;
  bool _isNotificationLoading = false;

  List<SmartAdModel> get activeAds => _activeAds;
  List<SmartAdModel> get pendingInAppBanners => _pendingInAppBanners;
  List<SmartAdNotificationModel> get notifications => _notifications;
  bool get isLoading => _isLoading;
  bool get isNotificationLoading => _isNotificationLoading;

  Future<void> saveFcmToken(String token, int? userId) async {
    ApiResponseModel apiResponse = await adService.saveFcmToken(
      token,
      userId,
      "android",
      "app",
    );
    if (apiResponse.response != null && apiResponse.response!.statusCode == 200) {
      debugPrint("FCM token saved successfully to SmartAds backend.");
    } else {
      debugPrint("Failed to save FCM token to SmartAds backend: ${apiResponse.error}");
    }
  }

  Future<void> getActiveAds({
    required String device,
    required String region,
    int? categoryId,
    int? userId,
  }) async {
    _isLoading = true;
    notifyListeners();

    ApiResponseModel apiResponse = await adService.getActiveAds(device, region, categoryId, userId);
    if (apiResponse.response != null && apiResponse.response!.statusCode == 200) {
      _activeAds = [];
      final data = apiResponse.response!.data;
      if (data is List) {
        for (var ad in data) {
          _activeAds.add(SmartAdModel.fromJson(ad));
        }
      } else if (data is Map<String, dynamic>) {
        _activeAds.add(SmartAdModel.fromJson(data));
      }
    } else {
      debugPrint("Error fetching active ads: ${apiResponse.error}");
    }

    _isLoading = false;
    notifyListeners();
  }

  Future<void> trackImpression(int adId) async {
    ApiResponseModel apiResponse = await adService.trackImpression(adId);
    if (apiResponse.response != null && apiResponse.response!.statusCode == 200) {
      debugPrint("Impression tracked for ad $adId");
    } else {
      debugPrint("Failed tracking impression for ad $adId");
    }
  }

  Future<void> trackClick(int adId) async {
    ApiResponseModel apiResponse = await adService.trackClick(adId);
    if (apiResponse.response != null && apiResponse.response!.statusCode == 200) {
      debugPrint("Click tracked for ad $adId");
    } else {
      debugPrint("Failed tracking click for ad $adId");
    }
  }

  Future<void> getPendingInAppBanners(int? userId) async {
    ApiResponseModel apiResponse = await adService.getPendingInAppBanners(userId, "android");
    if (apiResponse.response != null && apiResponse.response!.statusCode == 200) {
      _pendingInAppBanners = [];
      final data = apiResponse.response!.data;
      if (data is List) {
        for (var ad in data) {
          _pendingInAppBanners.add(SmartAdModel.fromJson(ad));
        }
      } else if (data is Map<String, dynamic>) {
        _pendingInAppBanners.add(SmartAdModel.fromJson(data));
      }
    }
    notifyListeners();
  }

  Future<void> getNotifications(int? userId) async {
    _isNotificationLoading = true;
    notifyListeners();

    ApiResponseModel apiResponse = await adService.getNotifications(userId);
    if (apiResponse.response != null && apiResponse.response!.statusCode == 200) {
      _notifications = [];
      final data = apiResponse.response!.data;
      if (data is List) {
        for (var notification in data) {
          _notifications.add(SmartAdNotificationModel.fromJson(notification));
        }
      }
    }
    _isNotificationLoading = false;
    notifyListeners();
  }

  Future<void> markNotificationAsRead(int notificationId) async {
    ApiResponseModel apiResponse = await adService.markNotificationAsRead(notificationId);
    if (apiResponse.response != null && apiResponse.response!.statusCode == 200) {
      final index = _notifications.indexWhere((element) => element.id == notificationId);
      if (index != -1) {
        _notifications[index].isRead = true;
        notifyListeners();
      }
    }
  }
}
