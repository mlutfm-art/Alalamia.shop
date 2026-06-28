import 'package:dio/dio.dart';
import 'package:flutter_sixvalley_ecommerce/data/datasource/remote/dio/dio_client.dart';
import 'package:flutter_sixvalley_ecommerce/data/datasource/remote/exception/api_error_handler.dart';
import 'package:flutter_sixvalley_ecommerce/data/model/api_response.dart';
import 'package:flutter_sixvalley_ecommerce/features/prediction/domain/repositories/prediction_repository_interface.dart';
import 'package:flutter_sixvalley_ecommerce/utill/app_constants.dart';

class PredictionRepository implements PredictionRepositoryInterface {
  final DioClient dioClient;
  PredictionRepository({required this.dioClient});

  @override
  Future<ApiResponseModel> getActiveMatch() async {
    try {
      Response response = await dioClient.get(AppConstants.activeMatchUri);
      return ApiResponseModel.withSuccess(response);
    } catch (e) {
      return ApiResponseModel.withError(ApiErrorHandler.getMessage(e));
    }
  }

  @override
  Future<ApiResponseModel> submitPrediction(int matchId, int team1Score, int team2Score) async {
    try {
      Response response = await dioClient.post(
        AppConstants.submitPredictionUri,
        data: {
          'match_id': matchId,
          'predicted_team1': team1Score,
          'predicted_team2': team2Score,
        },
      );
      return ApiResponseModel.withSuccess(response);
    } catch (e) {
      return ApiResponseModel.withError(ApiErrorHandler.getMessage(e));
    }
  }

  @override
  Future<ApiResponseModel> getLeaderboard() async {
    try {
      Response response = await dioClient.get(AppConstants.leaderboardUri);
      return ApiResponseModel.withSuccess(response);
    } catch (e) {
      return ApiResponseModel.withError(ApiErrorHandler.getMessage(e));
    }
  }

  @override
  Future<ApiResponseModel> getMyPredictions() async {
    try {
      Response response = await dioClient.get(AppConstants.myPredictionsUri);
      return ApiResponseModel.withSuccess(response);
    } catch (e) {
      return ApiResponseModel.withError(ApiErrorHandler.getMessage(e));
    }
  }

  @override
  Future<ApiResponseModel> getActiveBanner() async {
    try {
      Response response = await dioClient.get(AppConstants.predictionBannerUri);
      return ApiResponseModel.withSuccess(response);
    } catch (e) {
      return ApiResponseModel.withError(ApiErrorHandler.getMessage(e));
    }
  }

  @override
  Future add(value) {
    throw UnimplementedError();
  }

  @override
  Future delete(int id) {
    throw UnimplementedError();
  }

  @override
  Future get(String id) {
    throw UnimplementedError();
  }

  @override
  Future getList({int? offset}) {
    throw UnimplementedError();
  }

  @override
  Future update(Map<String, dynamic> body, int id) {
    throw UnimplementedError();
  }
}
