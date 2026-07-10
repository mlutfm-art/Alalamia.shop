import 'package:flutter/material.dart';
import 'package:flutter_sixvalley_ecommerce/features/smartads/domain/services/ad_admin_service.dart';

class AdAdminController extends ChangeNotifier {
  final AdAdminService service;
  AdAdminController({required this.service});

  bool _isLoading = false;
  String? _errorMessage;

  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;

  Future<bool> toggleStatus(int adId, bool status) async {
    _isLoading = true; notifyListeners();
    try{
      final res = await service.toggleStatus(adId, status);
      _errorMessage = null;
      return res;
    }catch(e){ _errorMessage = e.toString(); return false;} finally{ _isLoading = false; notifyListeners(); }
  }

  Future<bool> sendFirebase(Map<String,dynamic> payload) async {
    _isLoading = true; notifyListeners();
    try{ final res = await service.sendFirebase(payload); _errorMessage = null; return res; }catch(e){ _errorMessage = e.toString(); return false; } finally{ _isLoading = false; notifyListeners(); }
  }
}
