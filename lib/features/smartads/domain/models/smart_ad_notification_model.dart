class SmartAdNotificationModel {
  int? id;
  String? title;
  String? body;
  String? image;
  bool? isRead;
  DateTime? createdAt;
  ActionEngine? actionEngine;

  SmartAdNotificationModel({this.id, this.title, this.body, this.image, this.isRead, this.createdAt, this.actionEngine});

  factory SmartAdNotificationModel.fromJson(Map<String, dynamic> json) {
    return SmartAdNotificationModel(
      id: json['id'] != null ? int.tryParse(json['id'].toString()) : null,
      title: json['title']?.toString(),
      body: json['body']?.toString(),
      image: json['image']?.toString(),
      isRead: json['is_read'] == null ? null : (json['is_read'] == true || json['is_read'].toString() == '1'),
      createdAt: DateTime.tryParse(json['created_at']?.toString() ?? ''),
      actionEngine: json['action_engine'] != null ? ActionEngine.fromJson(json['action_engine']) : null,
    );
  }
}

class ActionEngine {
  String? type;
  Map<String, dynamic>? payload;
  String? deepLink;

  ActionEngine({this.type, this.payload, this.deepLink});

  factory ActionEngine.fromJson(Map<String, dynamic> json) {
    return ActionEngine(
      type: json['type']?.toString(),
      payload: json['payload'] != null ? Map<String, dynamic>.from(json['payload']) : null,
      deepLink: json['deep_link']?.toString(),
    );
  }
}
