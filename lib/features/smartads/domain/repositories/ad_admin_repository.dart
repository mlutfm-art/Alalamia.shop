import 'package:flutter_sixvalley_ecommerce/data/datasource/remote/dio/dio_client.dart';

class AdAdminRepository {
  final DioClient dioClient;
  AdAdminRepository({required this.dioClient});

  Future<bool> toggleStatus(int adId, bool status) async {
    try{
      await dioClient.post('/api/v1/smartads/admin/toggle-status', data: {'ad_id': adId, 'status': status ? 1 : 0});
      return true;
    }catch(_){return false;}
  }

  Future<bool> sendFirebase(Map<String,dynamic> payload) async {
    try{ await dioClient.post('/api/v1/smartads/admin/send-firebase', data: payload); return true;}catch(_){return false;}
  }

  Future<Map<String,dynamic>?> segmentPreview(Map<String,dynamic> body) async {
    try{ final res = await dioClient.post('/api/v1/smartads/admin/segment-preview', data: body); return res; }catch(_){return null;}
  }

  Future<bool> segmentSendNow(Map<String,dynamic> body) async { try{ await dioClient.post('/api/v1/smartads/admin/segment-send', data: body); return true;}catch(_){return false;} }
  Future<bool> manualDeliver(Map<String,dynamic> body) async { try{ await dioClient.post('/api/v1/smartads/admin/manual-deliver', data: body); return true;}catch(_){return false;} }
  Future<List<dynamic>?> getActiveAdsAdmin() async { try{ final res = await dioClient.get('/api/v1/smartads/admin/active'); return res; }catch(_){return null;} }
  Future<List<dynamic>?> searchProducts(String q) async { try{ final res = await dioClient.get('/api/v1/products/filter?name=$q'); return res; }catch(_){return null;} }
}
