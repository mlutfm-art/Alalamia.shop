import 'package:flutter/material.dart';
import 'package:flutter_sixvalley_ecommerce/features/smartads/domain/services/ad_service.dart';

class AdController extends ChangeNotifier {
  final AdService adService;
  AdController({required this.adService});

  bool _isLoading = false;
  String? _errorMessage;
  List<dynamic> _dataList = [];

  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;
  List<dynamic> get dataList => _dataList;

  Future<void> fetchAdsByType(String type) async {
    _isLoading = true; notifyListeners();
    try {
      // placeholder: call service
      _dataList = [];
      _errorMessage = null;
    } catch (e) {
      _errorMessage = e.toString();
    }
    _isLoading = false; notifyListeners();
  }

  Future<void> fetchAdById(int id) async {
    _isLoading = true; notifyListeners();
    try{
      // placeholder
      _errorMessage = null;
    }catch(e){ _errorMessage = e.toString(); }
    _isLoading = false; notifyListeners();
  }

  Future<bool> confirmDose(int logId) async {
    _isLoading = true; notifyListeners();
    final ok = await adService.confirmDose(logId);
    _isLoading = false; notifyListeners();
    return ok;
  }

  Future<bool> snoozeDose(int logId) async {
    _isLoading = true; notifyListeners();
    final ok = await adService.snoozeDose(logId);
    _isLoading = false; notifyListeners();
    return ok;
  }
}
