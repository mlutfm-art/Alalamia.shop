import 'package:flutter_sixvalley_ecommerce/data/datasource/remote/dio/dio_client.dart';

class FcmRepository {
  final DioClient dioClient;
  FcmRepository({required this.dioClient});

  Future<bool> saveToken(String token) async {
    try{
      await dioClient.post('/api/v1/smartads/fcm/token/save', data: {'token': token});
      return true;
    }catch(_){return false;}
  }

  Future<bool> deleteToken(String token) async {
    try{ await dioClient.post('/api/v1/smartads/fcm/token/delete', data: {'token': token}); return true;}catch(_){return false;}
  }
}
