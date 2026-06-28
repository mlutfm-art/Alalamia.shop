import 'dart:async';
import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:flutter/scheduler.dart';
import 'package:flutter_sixvalley_ecommerce/data/model/api_response.dart';
import 'package:flutter_sixvalley_ecommerce/features/deal/domain/models/flash_deal_model.dart';
import 'package:flutter_sixvalley_ecommerce/features/deal/domain/services/flash_deal_service_interface.dart';
import 'package:flutter_sixvalley_ecommerce/features/product/domain/models/product_model.dart';
import 'package:flutter_sixvalley_ecommerce/main.dart';
import 'package:flutter_sixvalley_ecommerce/utill/app_constants.dart';
import 'package:intl/intl.dart';

class FlashDealController extends ChangeNotifier {
  final FlashDealServiceInterface flashDealServiceInterface;
  FlashDealController({required this.flashDealServiceInterface});

  FlashDealModel? _flashDeal;
  final List<Product> _flashDealList = [];
  
  Duration? get duration => durationNotifier.value;
  final ValueNotifier<Duration?> durationNotifier = ValueNotifier<Duration?>(null);
  final ValueNotifier<int> currentIndexNotifier = ValueNotifier<int>(0);
  
  Timer? _timer;
  FlashDealModel? get flashDeal => _flashDeal;
  List<Product> get flashDealList => _flashDealList;
  int get currentIndex => currentIndexNotifier.value;

  bool _isDisposed = false;

  @override
  void dispose() {
    _isDisposed = true;
    _timer?.cancel();
    durationNotifier.dispose();
    currentIndexNotifier.dispose();
    super.dispose();
  }

  void _safeNotify() {
    if (!_isDisposed) {
      if (SchedulerBinding.instance.schedulerPhase != SchedulerPhase.idle) {
        SchedulerBinding.instance.addPostFrameCallback((_) {
          if (!_isDisposed) notifyListeners();
        });
      } else {
        notifyListeners();
      }
    }
  }

  Future<void> getFlashDealList(bool reload, bool notify) async {
    var localData = await database.getCacheResponseById(AppConstants.flashDealUri);
    if(localData != null && _flashDeal == null) {
      try {
        _flashDeal = FlashDealModel.fromJson(jsonDecode(localData.response));
        _updateTimer();
        _safeNotify();
      } catch (e) {
        debugPrint("FlashDeal Cache Error: $e");
      }
    }

    if (_flashDealList.isEmpty || reload) {
      ApiResponseModel apiResponse = await flashDealServiceInterface.getFlashDeal();
      if (apiResponse.response?.statusCode == 200 && apiResponse.response?.data != null) {
        _flashDeal = FlashDealModel.fromJson(apiResponse.response!.data);
        _updateTimer();
        
        if(_flashDeal?.id != null) {
          ApiResponseModel megaDealResponse = await flashDealServiceInterface.get(_flashDeal!.id.toString());
          if (megaDealResponse.response?.statusCode == 200) {
            _flashDealList.clear();
            if (megaDealResponse.response!.data is List) {
              for (var p in megaDealResponse.response!.data) {
                _flashDealList.add(Product.fromJson(p));
              }
            }
          }
        }
      } else {
        // في حال الخطأ، نضع كائن فارغ لمنع الـ Shimmer اللانهائي
        _flashDeal = FlashDealModel(id: -1); 
      }
      _safeNotify();
    }
  }

  void _updateTimer() {
    if (_flashDeal?.endDate != null) {
      DateTime? endTime;
      try {
        endTime = DateFormat("yyyy-MM-dd").parse(_flashDeal!.endDate!).add(const Duration(days: 1));
      } catch (e) {
        endTime = DateTime.tryParse(_flashDeal!.endDate!);
      }
      
      if (endTime != null) {
        final now = DateTime.now();
        if (endTime.isAfter(now)) {
          durationNotifier.value = endTime.difference(now);
          _startTimer();
        } else {
          durationNotifier.value = Duration.zero;
        }
      }
    }
  }

  void _startTimer() {
    _timer?.cancel();
    _timer = Timer.periodic(const Duration(seconds: 1), (timer) {
      if (_isDisposed) {
        timer.cancel();
        return;
      }
      if (durationNotifier.value != null && durationNotifier.value!.inSeconds > 0) {
        durationNotifier.value = durationNotifier.value! - const Duration(seconds: 1);
      } else {
        durationNotifier.value = Duration.zero;
        _timer?.cancel();
      }
    });
  }

  void setCurrentIndex(int index) {
    if (currentIndexNotifier.value != index) {
      currentIndexNotifier.value = index;
    }
  }
}
