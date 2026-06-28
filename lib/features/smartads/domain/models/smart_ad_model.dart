class SmartAdModel {
  int? id;
  String? title;
  String? description;
  String? image;
  String? url;
  String? placement;
  String? type;
  String? videoUrl;
  
  // Display properties
  String? backgroundColor;
  String? textColor;
  String? buttonText;
  int? durationMs;
  bool? dismissible;
  double? overlayOpacity;
  String? position;

  SmartAdModel({
    this.id,
    this.title,
    this.description,
    this.image,
    this.url,
    this.placement,
    this.type,
    this.videoUrl,
    this.backgroundColor,
    this.textColor,
    this.buttonText,
    this.durationMs,
    this.dismissible,
    this.overlayOpacity,
    this.position,
  });

  SmartAdModel.fromJson(Map<String, dynamic> json) {
    id = int.tryParse(json['id']?.toString() ?? '');
    title = json['title'];
    
    // image should map json['image'] or json['image_url']
    image = json['image'] ?? json['image_url'];
    
    // type should map json['type'] or json['ad_type']
    type = json['type'] ?? json['ad_type'];
    
    videoUrl = json['video_url'];
    placement = json['placement'];

    // url mapping
    if (json['action'] != null && json['action'] is Map) {
      url = json['url'] ?? json['action']['fallback_url'] ?? json['action']['deep_link'];
    } else {
      url = json['url'];
    }

    // description & display mapping
    if (json['display'] != null && json['display'] is Map) {
      description = json['description'] ?? json['display']['description'] ?? json['display']['subtitle'];
      backgroundColor = json['display']['background_color'];
      textColor = json['display']['text_color'];
      buttonText = json['display']['button_text'];
      durationMs = json['display']['duration_ms'];
      dismissible = json['display']['dismissible'] ?? true;
      overlayOpacity = double.tryParse(json['display']['overlay_opacity']?.toString() ?? '0.0');
      position = json['display']['position'];
    } else {
      description = json['description'];
      dismissible = true;
    }
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id, 'title': title, 'description': description,
      'image': image, 'url': url, 'placement': placement,
      'type': type, 'video_url': videoUrl,
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
    image = json['image'] ?? json['image_url'];
    isRead = json['is_read'] == true || json['is_read'] == 1 || json['is_read'] == '1';
    createdAt = json['created_at'];
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id, 'title': title, 'body': body, 'image': image,
      'is_read': (isRead ?? false) ? 1 : 0, 'created_at': createdAt,
    };
  }
}
