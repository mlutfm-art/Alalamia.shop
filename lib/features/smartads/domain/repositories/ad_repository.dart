import 'package:shared_preferences/shared_preferences.dart';
import 'package:flutter_sixvalley_ecommerce/data/datasource/remote/dio/dio_client.dart';

class AdRepository {
  final DioClient dioClient;
  final SharedPreferences sharedPreferences;
  AdRepository({required this.dioClient, required this.sharedPreferences});

  Future<List<dynamic>> getActiveAds() async {
    final res = await dioClient.get("${Uri.parse("")}");
    return [];
  }

  Future<bool> confirmDose(int logId) async {
    // placeholder
    try {
      final resp = await dioClient.post('/api/v1/smartads/confirm-dose', data: {'log_id': logId});
      return resp != null;
    } catch(_) { return false; }
  }

  Future<bool> snoozeDose(int logId) async {
    try{
      final resp = await dioClient.post('/api/v1/smartads/snooze-dose', data: {'log_id': logId});
      return resp != null;
    }catch(_){return false;}
  }
}
