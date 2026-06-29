import 'package:flutter_sixvalley_ecommerce/features/smartads/domain/models/smart_ad_model.dart';

class SmartAdNotificationModel {
  int? id;
  String? title;
  String? body;
  String? image;
  String? actionType;
  Map<String, dynamic>? actionPayload;
  bool? isRead;
  DateTime? createdAt;

  SmartAdNotificationModel({
    this.id,
    this.title,
    this.body,
    this.image,
    this.actionType,
    this.actionPayload,
    this.isRead,
    this.createdAt,
  });

  SmartAdNotificationModel.fromJson(Map<String, dynamic> json) {
    id = int.tryParse(json['id']?.toString() ?? '');
    title = json['title'];
    body = json['body'];
    image = json['image_url'] ?? json['image'];
    actionType = json['action_type'];
    if (json['action_data'] != null) {
      actionPayload = json['action_data'] is String 
        ? null // Add decoding logic if needed
        : json['action_data'];
    }
    isRead = json['is_read'] == 1 || json['is_read'] == true;
    createdAt = json['created_at'] != null ? DateTime.tryParse(json['created_at']) : null;
  }
}
