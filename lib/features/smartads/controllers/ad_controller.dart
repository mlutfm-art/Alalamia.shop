import 'package:flutter/material.dart';
import 'package:flutter_sixvalley_ecommerce/data/model/api_response.dart';
import 'package:flutter_sixvalley_ecommerce/features/smartads/domain/models/smart_ad_model.dart';
import 'package:flutter_sixvalley_ecommerce/features/smartads/domain/models/smart_ad_notification_model.dart';
import 'package:flutter_sixvalley_ecommerce/features/smartads/domain/services/ad_service_interface.dart';

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

  Future<void> getActiveAds({required String device, required String region, int? categoryId, int? userId}) async {
    _isLoading = true;
    notifyListeners();
    try {
      ApiResponseModel apiResponse = await adService.getActiveAds(device, region, categoryId, userId);
      if (apiResponse.response != null && apiResponse.response!.statusCode == 200) {
        _activeAds = [];
        final dynamic responseData = apiResponse.response!.data;
        final dynamic rawList = (responseData is Map && responseData.containsKey('data')) ? responseData['data'] : responseData;
        
        if (rawList is List) {
          for (var ad in rawList) {
            _activeAds.add(SmartAdModel.fromJson(ad));
          }
        }
        debugPrint("SmartAds: Fetched ${_activeAds.length} active ads from server.");
      }
    } catch (e) {
      debugPrint("SmartAds Error: $e");
    }
    _isLoading = false;
    notifyListeners();
  }

  Future<void> getPendingInAppBanners(int? userId) async {
    ApiResponseModel apiResponse = await adService.getPendingInAppBanners(userId, "android");
    if (apiResponse.response != null && apiResponse.response!.statusCode == 200) {
      _pendingInAppBanners = [];
      final dynamic responseData = apiResponse.response!.data;
      final dynamic rawList = (responseData is Map && responseData.containsKey('data')) ? responseData['data'] : responseData;
      if (rawList is List) {
        for (var ad in rawList) {
          _pendingInAppBanners.add(SmartAdModel.fromJson(ad));
        }
      }
    }
    notifyListeners();
  }

  Future<void> trackImpression(int adId) async => await adService.trackImpression(adId);
  Future<void> trackClick(int adId) async => await adService.trackClick(adId);

  Future<void> getNotifications(int? userId) async {
    _isNotificationLoading = true;
    notifyListeners();
    
    try {
      ApiResponseModel apiResponse = await adService.getNotifications(userId);
      if (apiResponse.response != null && apiResponse.response!.statusCode == 200) {
        _notifications = [];
        final dynamic responseData = apiResponse.response!.data;
        final dynamic rawList = (responseData is Map && responseData.containsKey('data')) 
            ? responseData['data'] : responseData;
        if (rawList is List) {
          for (var n in rawList) {
            _notifications.add(SmartAdNotificationModel.fromJson(n));
          }
        }
      }
    } catch (e) {
      debugPrint("SmartAds Notifications Error: $e");
    }
    
    _isNotificationLoading = false;
    notifyListeners();
  }

  Future<void> markNotificationAsRead(int id) async {
    ApiResponseModel apiResponse = await adService.markNotificationAsRead(id);
    if (apiResponse.response != null && apiResponse.response!.statusCode == 200) {
      final index = _notifications.indexWhere((element) => element.id == id);
      if (index != -1) {
        _notifications[index].isRead = true;
        notifyListeners();
      }
    }
  }
}
