class SmartAdModel {
  int? id;
  String? title;
  String? description;
  String? image;
  String? url;
  String? placement; // e.g., 'home', 'category', etc.
  String? type; // banner, popup, etc.
  int? storeId;
  String? targetType;
  String? targetValue;

  SmartAdModel({
    this.id,
    this.title,
    this.description,
    this.image,
    this.url,
    this.placement,
    this.type,
    this.storeId,
    this.targetType,
    this.targetValue,
  });

  SmartAdModel.fromJson(Map<String, dynamic> json) {
    id = int.tryParse(json['id']?.toString() ?? '');
    title = json['title'];
    description = json['description'];
    image = json['image'];
    url = json['url'];
    placement = json['placement'];
    type = json['type'];
    storeId = int.tryParse(json['store_id']?.toString() ?? '');
    targetType = json['target_type'];
    targetValue = json['target_value'];
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'title': title,
      'description': description,
      'image': image,
      'url': url,
      'placement': placement,
      'type': type,
      'store_id': storeId,
      'target_type': targetType,
      'target_value': targetValue,
    };
  }
}

class SmartAdNotificationModel {
  int? id;
  String? title;
  String? body;
  String? image;
  bool? isRead;
  String? createdAt;

  SmartAdNotificationModel({
    this.id,
    this.title,
    this.body,
    this.image,
    this.isRead,
    this.createdAt,
  });

  SmartAdNotificationModel.fromJson(Map<String, dynamic> json) {
    id = int.tryParse(json['id']?.toString() ?? '');
    title = json['title'];
    body = json['body'];
    image = json['image'];
    isRead = json['is_read'] == true || json['is_read'] == 1 || json['is_read'] == '1';
    createdAt = json['created_at'];
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'title': title,
      'body': body,
      'image': image,
      'is_read': (isRead ?? false) ? 1 : 0,
      'created_at': createdAt,
    };
  }
}
