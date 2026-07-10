import 'package:flutter_sixvalley_ecommerce/features/smartads/domain/repositories/ad_admin_repository.dart';

class AdAdminService {
  final AdAdminRepository repo;
  AdAdminService({required this.repo});

  Future<bool> toggleStatus(int adId, bool status) async => await repo.toggleStatus(adId, status);
  Future<bool> sendFirebase(Map<String,dynamic> payload) async => await repo.sendFirebase(payload);
  Future<Map<String,dynamic>?> segmentPreview(Map<String,dynamic> body) async => await repo.segmentPreview(body);
  Future<bool> segmentSendNow(Map<String,dynamic> body) async => await repo.segmentSendNow(body);
  Future<bool> manualDeliver(Map<String,dynamic> body) async => await repo.manualDeliver(body);
  Future<List<dynamic>?> getActiveAdsAdmin() async => await repo.getActiveAdsAdmin();
  Future<List<dynamic>?> searchProducts(String q) async => await repo.searchProducts(q);
}
