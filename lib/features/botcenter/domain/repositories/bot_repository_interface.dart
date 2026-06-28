import 'package:flutter_sixvalley_ecommerce/data/model/api_response.dart';

abstract class BotRepositoryInterface {
  Future<ApiResponseModel> queryBot(String message, int storeId, String sessionId, String pageContext);
  Future<ApiResponseModel> getConversationLog(String conversationId);
  Future<ApiResponseModel> sendFeedback(int messageId, String rating, String? feedbackText);
  Future<ApiResponseModel> getQuickActions();
}
