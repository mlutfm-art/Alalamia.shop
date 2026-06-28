class SmartAdModel {
  int? id;
  String? title;
  String? description;
  String? image;
  String? url;
  String? placement;
  String? type;
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
    
    // تصحيح جلب الصورة
    image = json['image_url'] ?? json['image'];
    
    // تصحيح جلب النوع
    type = json['ad_type'] ?? json['type'];
    
    // تصحيح جلب الرابط من action -> fallback_url أو deep_link
    if (json['action'] != null && json['action'] is Map) {
      url = json['action']['fallback_url'] ?? json['action']['deep_link'] ?? json['url'];
    } else {
      url = json['url'];
    }
    
    // تصحيح جلب الوصف من display -> description أو subtitle
    if (json['display'] != null && json['display'] is Map) {
      description = json['display']['description'] ?? json['display']['subtitle'] ?? json['description'];
    } else {
      description = json['description'];
    }
    
    placement = json['placement'];
    storeId = int.tryParse(json['store_id']?.toString() ?? '');
    
    if (json['targeting'] != null && json['targeting'] is Map) {
      targetType = json['targeting']['device_type']?.toString();
      targetValue = json['targeting']['region']?.toString();
    }
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id, 'title': title, 'description': description,
      'image': image, 'url': url, 'placement': placement,
      'type': type, 'store_id': storeId,
    };
  }
}
