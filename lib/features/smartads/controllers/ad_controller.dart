import 'package:flutter/material.dart';
import 'package:flutter_sixvalley_ecommerce/data/model/api_response.dart';
import 'package:flutter_sixvalley_ecommerce/features/smartads/domain/models/smart_ad_model.dart';
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
      final responseBody = apiResponse.response!.data;
      
      // الدخول لمفتاح data القادم من الباك-إند
      final dynamic rawData = (responseBody is Map && responseBody.containsKey('data')) 
          ? responseBody['data'] 
          : responseBody;

      if (rawData is List) {
        for (var ad in rawData) {
          _activeAds.add(SmartAdModel.fromJson(ad));
        }
      } else if (rawData is Map<String, dynamic>) {
        _activeAds.add(SmartAdModel.fromJson(rawData));
      }
      debugPrint("SmartAds: Fetched ${_activeAds.length} active ads");
    }
    _isLoading = false;
    notifyListeners();
  }

  Future<void> getPendingInAppBanners(int? userId) async {
    ApiResponseModel apiResponse = await adService.getPendingInAppBanners(userId, "android");
    if (apiResponse.response != null && apiResponse.response!.statusCode == 200) {
      _pendingInAppBanners = [];
      final responseBody = apiResponse.response!.data;
      final dynamic rawData = (responseBody is Map && responseBody.containsKey('data')) 
          ? responseBody['data'] 
          : responseBody;

      if (rawData is List) {
        for (var ad in rawData) {
          _pendingInAppBanners.add(SmartAdModel.fromJson(ad));
        }
      }
    }
    notifyListeners();
  }

  Future<void> trackImpression(int adId) async => await adService.trackImpression(adId);
  Future<void> trackClick(int adId) async => await adService.trackClick(adId);
}
