import 'package:flutter_sixvalley_ecommerce/data/model/api_response.dart';
import 'package:flutter_sixvalley_ecommerce/features/botcenter/domain/repositories/bot_repository_interface.dart';
import 'package:flutter_sixvalley_ecommerce/features/botcenter/domain/services/bot_service_interface.dart';

class BotService implements BotServiceInterface {
  final BotRepositoryInterface botRepository;
  BotService({required this.botRepository});

  @override
  Future<ApiResponseModel> queryBot(String message, int storeId, String sessionId, String pageContext) {
    return botRepository.queryBot(message, storeId, sessionId, pageContext);
  }

  @override
  Future<ApiResponseModel> getConversationLog(String conversationId) {
    return botRepository.getConversationLog(conversationId);
  }

  @override
  Future<ApiResponseModel> sendFeedback(int messageId, String rating, String? feedbackText) {
    return botRepository.sendFeedback(messageId, rating, feedbackText);
  }
}
