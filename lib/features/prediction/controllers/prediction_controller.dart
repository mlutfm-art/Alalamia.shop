import 'package:flutter/material.dart';
import 'package:flutter_sixvalley_ecommerce/data/model/api_response.dart';
import 'package:flutter_sixvalley_ecommerce/features/prediction/domain/models/prediction_model.dart';
import 'package:flutter_sixvalley_ecommerce/features/prediction/domain/repositories/prediction_repository_interface.dart';
import 'package:flutter_sixvalley_ecommerce/helper/api_checker.dart';
import 'package:flutter_sixvalley_ecommerce/common/basewidget/show_custom_snakbar_widget.dart';
import 'package:flutter_sixvalley_ecommerce/main.dart';

class PredictionController extends ChangeNotifier {
  final PredictionRepositoryInterface predictionRepo;
  PredictionController({required this.predictionRepo});

  List<MatchModel> _activeMatches = [];
  List<PredictionModel> _myPredictions = [];
  PredictionBannerModel? _predictionBanner;
  bool _isLoading = false;
  bool _isSubmitLoading = false;

  List<MatchModel> get activeMatches => _activeMatches;
  List<PredictionModel> get myPredictions => _myPredictions;
  PredictionBannerModel? get predictionBanner => _predictionBanner;
  bool get isLoading => _isLoading;
  bool get isSubmitLoading => _isSubmitLoading;

  MatchModel? get activeMatch => _activeMatches.isNotEmpty ? _activeMatches.first : null;

  Future<void> getMatchList() async {
    _isLoading = true;
    notifyListeners();
    ApiResponseModel apiResponse = await predictionRepo.getActiveMatch();
    if (apiResponse.response != null && apiResponse.response!.statusCode == 200) {
      _activeMatches = [];
      final data = apiResponse.response!.data;
      if (data is List) {
        for (var m in data) {
          _activeMatches.add(MatchModel.fromJson(m));
        }
      } else if (data is Map) {
        _activeMatches.add(MatchModel.fromJson(data as Map<String, dynamic>));
      }
    } else {
      ApiChecker.checkApi(apiResponse);
    }
    _isLoading = false;
    notifyListeners();
  }

  Future<void> submitPrediction(int matchId, int score1, int score2) async {
    _isSubmitLoading = true;
    notifyListeners();
    ApiResponseModel apiResponse = await predictionRepo.submitPrediction(matchId, score1, score2);
    
    if (apiResponse.response != null && (apiResponse.response!.statusCode == 200 || apiResponse.response!.statusCode == 201)) {
      
      // تم تصحيح المعامل هنا من isError إلى snackBarType
      showCustomSnackBarWidget("تم إرسال التوقع بنجاح! ⚽", Get.context!, snackBarType: SnackBarType.success);
      
      getMatchList(); 
      getMyPredictions(); 
    } else {
      ApiChecker.checkApi(apiResponse);
    }
    _isSubmitLoading = false;
    notifyListeners();
  }

  Future<void> getMyPredictions() async {
    _isLoading = true;
    notifyListeners();
    ApiResponseModel apiResponse = await predictionRepo.getMyPredictions();
    if (apiResponse.response != null && apiResponse.response!.statusCode == 200) {
      _myPredictions = [];
      if (apiResponse.response!.data is List) {
        for (var p in apiResponse.response!.data) {
          _myPredictions.add(PredictionModel.fromJson(p));
        }
      }
    }
    _isLoading = false;
    notifyListeners();
  }

  Future<void> getPredictionBanner() async {
    ApiResponseModel apiResponse = await predictionRepo.getActiveBanner();
    if (apiResponse.response != null && apiResponse.response!.statusCode == 200) {
      try {
        final dynamic data = apiResponse.response!.data;
        if (data is Map<String, dynamic>) {
          _predictionBanner = PredictionBannerModel.fromJson(data);
        }
      } catch (e) {
        debugPrint("Prediction Banner Parsing Error: $e");
      }
    }
    notifyListeners();
  }

  Future<void> getLeaderboard() async {}
}
