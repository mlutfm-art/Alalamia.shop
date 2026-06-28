class BotMessageModel {
  int? id; // message id from server
  String? message;
  bool isBot;
  String? feedback; // 'useful' or 'not_useful'
  String? feedbackText;
  DateTime? createdAt;

  BotMessageModel({
    this.id,
    this.message,
    required this.isBot,
    this.feedback,
    this.feedbackText,
    this.createdAt,
  });

  BotMessageModel.fromJson(Map<String, dynamic> json)
      : id = int.tryParse(json['id']?.toString() ?? json['message_id']?.toString() ?? ''),
        message = json['message'] ?? json['response'] ?? '',
        isBot = json['is_bot'] == true || json['is_bot'] == 1 || json['sender'] == 'bot' || json['isBot'] == true,
        feedback = json['feedback'] ?? json['rating'],
        feedbackText = json['feedback_text'],
        createdAt = json['created_at'] != null ? DateTime.tryParse(json['created_at'].toString()) : null;

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'message': message,
      'is_bot': isBot ? 1 : 0,
      'feedback': feedback,
      'feedback_text': feedbackText,
      'created_at': createdAt?.toIso8601String(),
    };
  }
}
