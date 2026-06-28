import 'package:dio/dio.dart';
import 'package:flutter_sixvalley_ecommerce/data/datasource/remote/dio/dio_client.dart';
import 'package:flutter_sixvalley_ecommerce/data/datasource/remote/exception/api_error_handler.dart';
import 'package:flutter_sixvalley_ecommerce/data/model/api_response.dart';
import 'package:flutter_sixvalley_ecommerce/features/botcenter/domain/repositories/bot_repository_interface.dart';
import 'package:flutter_sixvalley_ecommerce/utill/app_constants.dart';

class BotRepository implements BotRepositoryInterface {
  final DioClient dioClient;
  BotRepository({required this.dioClient});

  @override
  Future<ApiResponseModel> queryBot(String message, int storeId, String sessionId, String pageContext) async {
    try {
      Response response = await dioClient.post(
        AppConstants.botQueryUri,
        data: {
          "message": message,
          "store_id": storeId,
          "session_id": sessionId,
          "context": {
            "page": pageContext,
          }
        },
      );
      return ApiResponseModel.withSuccess(response);
    } catch (e) {
      return ApiResponseModel.withError(ApiErrorHandler.getMessage(e));
    }
  }

  @override
  Future<ApiResponseModel> getConversationLog(String conversationId) async {
    try {
      Response response = await dioClient.get(
        '${AppConstants.botConversationUri}/$conversationId',
      );
      return ApiResponseModel.withSuccess(response);
    } catch (e) {
      return ApiResponseModel.withError(ApiErrorHandler.getMessage(e));
    }
  }

  @override
  Future<ApiResponseModel> sendFeedback(int messageId, String rating, String? feedbackText) async {
    try {
      Response response = await dioClient.post(
        AppConstants.botFeedbackUri,
        data: {
          "message_id": messageId,
          "rating": rating,
          if (feedbackText != null && feedbackText.isNotEmpty) "feedback_text": feedbackText,
        },
      );
      return ApiResponseModel.withSuccess(response);
    } catch (e) {
      return ApiResponseModel.withError(ApiErrorHandler.getMessage(e));
    }
  }

  @override
  Future<ApiResponseModel> getQuickActions() async {
    try {
      Response response = await dioClient.get(AppConstants.botQuickActionsUri);
      return ApiResponseModel.withSuccess(response);
    } catch (e) {
      return ApiResponseModel.withError(ApiErrorHandler.getMessage(e));
    }
  }
}
