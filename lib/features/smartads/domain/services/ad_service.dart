import 'package:flutter_sixvalley_ecommerce/features/smartads/domain/repositories/ad_repository.dart';

class AdService {
  final AdRepository adRepository;
  AdService({required this.adRepository});

  Future<bool> confirmDose(int logId) async => await adRepository.confirmDose(logId);
  Future<bool> snoozeDose(int logId) async => await adRepository.snoozeDose(logId);
}
