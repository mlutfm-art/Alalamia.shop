import 'package:flutter_sixvalley_ecommerce/features/smartads/domain/repositories/fcm_repository.dart';

class FcmService {
  final FcmRepository repo;
  FcmService({required this.repo});

  Future<bool> saveToken(String token) async => await repo.saveToken(token);
  Future<bool> deleteToken(String token) async => await repo.deleteToken(token);
}
