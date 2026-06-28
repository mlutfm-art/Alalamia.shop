import 'dart:math';
import 'package:flutter/material.dart';
import 'package:flutter_sixvalley_ecommerce/data/model/api_response.dart';
import 'package:flutter_sixvalley_ecommerce/features/botcenter/domain/models/bot_message_model.dart';
import 'package:flutter_sixvalley_ecommerce/features/botcenter/domain/services/bot_service_interface.dart';
import 'package:shared_preferences/shared_preferences.dart';

class BotController extends ChangeNotifier {
  final BotServiceInterface botService;
  final SharedPreferences sharedPreferences;
  BotController({required this.botService, required this.sharedPreferences});

  static const String _sessionIdKey = 'bot_session_id';

  List<BotMessageModel> _messages = [];
  bool _isBotTyping = false;
  String? _sessionId;
  String _currentPage = 'home';

  List<BotMessageModel> get messages => _messages;
  bool get isBotTyping => _isBotTyping;
  String get sessionId => _sessionId ?? '';

  void setCurrentPage(String page) {
    _currentPage = page;
  }

  String _getOrCreateSessionId() {
    if (_sessionId != null && _sessionId!.isNotEmpty) return _sessionId!;
    _sessionId = sharedPreferences.getString(_sessionIdKey);
    if (_sessionId == null || _sessionId!.isEmpty) {
      _sessionId = _generateUUID();
      sharedPreferences.setString(_sessionIdKey, _sessionId!);
    }
    return _sessionId!;
  }

  String _generateUUID() {
    final random = Random.secure();
    final bytes = List<int>.generate(16, (_) => random.nextInt(256));
    bytes[6] = (bytes[6] & 0x0f) | 0x40;
    bytes[8] = (bytes[8] & 0x3f) | 0x80;
    final hex = bytes.map((b) => b.toRadixString(16).padLeft(2, '0')).join();
    return '${hex.substring(0, 8)}-${hex.substring(8, 12)}-${hex.substring(12, 16)}-${hex.substring(16, 20)}-${hex.substring(20)}';
  }

  void resetSession() {
    _sessionId = _generateUUID();
    sharedPreferences.setString(_sessionIdKey, _sessionId!);
    _messages = [];
    notifyListeners();
  }

  Future<void> sendMessage(String message, {int storeId = 1}) async {
    if (message.trim().isEmpty) return;

    // Add user message locally
    _messages.add(BotMessageModel(
      message: message.trim(),
      isBot: false,
      createdAt: DateTime.now(),
    ));
    _isBotTyping = true;
    notifyListeners();

    final sid = _getOrCreateSessionId();

    ApiResponseModel apiResponse = await botService.queryBot(
      message.trim(),
      storeId,
      sid,
      _currentPage,
    );

    _isBotTyping = false;

    if (apiResponse.response != null &&
        (apiResponse.response!.statusCode == 200 || apiResponse.response!.statusCode == 201)) {
      final data = apiResponse.response!.data;
      String botReply = '';
      int? messageId;

      if (data is Map<String, dynamic>) {
        botReply = data['response']?.toString() ?? data['message']?.toString() ?? '';
        messageId = int.tryParse(data['message_id']?.toString() ?? '');
      }

      _messages.add(BotMessageModel(
        id: messageId,
        message: botReply.isNotEmpty ? botReply : 'عذراً، لم أتمكن من فهم طلبك.',
        isBot: true,
        createdAt: DateTime.now(),
      ));
    } else {
      _messages.add(BotMessageModel(
        message: 'عذراً، حدث خطأ في الاتصال. حاول مرة أخرى.',
        isBot: true,
        createdAt: DateTime.now(),
      ));
    }

    notifyListeners();
  }

  Future<void> loadConversationLog(String conversationId) async {
    ApiResponseModel apiResponse = await botService.getConversationLog(conversationId);
    if (apiResponse.response != null && apiResponse.response!.statusCode == 200) {
      final data = apiResponse.response!.data;
      if (data is List) {
        _messages = data.map((m) => BotMessageModel.fromJson(m)).toList();
      } else if (data is Map<String, dynamic> && data['messages'] is List) {
        _messages = (data['messages'] as List).map((m) => BotMessageModel.fromJson(m)).toList();
      }
      notifyListeners();
    }
  }

  Future<void> sendFeedback(int messageId, String rating, {String? feedbackText}) async {
    ApiResponseModel apiResponse = await botService.sendFeedback(messageId, rating, feedbackText);
    if (apiResponse.response != null &&
        (apiResponse.response!.statusCode == 200 || apiResponse.response!.statusCode == 201)) {
      final index = _messages.indexWhere((m) => m.id == messageId);
      if (index != -1) {
        _messages[index].feedback = rating;
        _messages[index].feedbackText = feedbackText;
        notifyListeners();
      }
    }
  }
}
