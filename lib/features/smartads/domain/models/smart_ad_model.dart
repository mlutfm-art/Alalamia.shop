class SmartAdModel {
  int? id;
  String? title;
  String? description;
  String? image;
  String? url;
  String? placement;
  String? type;
  String? backgroundColor;
  String? textColor;
  String? buttonText;
  Map<String, dynamic>? actionPayload;

  SmartAdModel({
    this.id,
    this.title,
    this.description,
    this.image,
    this.url,
    this.placement,
    this.type,
    this.backgroundColor,
    this.textColor,
    this.buttonText,
    this.actionPayload,
  });

  SmartAdModel.fromJson(Map<String, dynamic> json) {
    id = int.tryParse(json['id']?.toString() ?? '');
    title = json['title'];
    image = json['image_url'] ?? json['image'];
    type = json['ad_type'] ?? json['type'];
    placement = json['placement'];
    
    // ربط محرك التفاعل (Action Engine)
    if (json['action_engine'] != null) {
      actionPayload = json['action_engine'];
      url = json['action_engine']['deep_link'] ?? json['action_engine']['fallback_url'];
    }

    // ربط إعدادات العرض
    if (json['display_settings'] != null) {
      backgroundColor = json['display_settings']['background_color'];
      textColor = json['display_settings']['text_color'];
      buttonText = json['display_settings']['button_text'];
    }
    
    description = json['description'] ?? (json['display_settings'] != null ? json['display_settings']['subtitle'] : null);
  }
}
