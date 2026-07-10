import 'package:flutter_sixvalley_ecommerce/features/smartads/domain/models/action_data_model.dart';

class SmartAdModel {
  int? id;
  String? title;
  String? subTitle;
  String? image;
  String? imageUrl;
  String? img;
  String? picture;
  String? photo;
  String? videoUrl;
  String? adType;
  String? placement;
  String? buttonText;
  String? actionType;
  ActionData? actionData;
  Map<String, dynamic>? displaySettings;
  String? deepLink;
  DateTime? createdAt;
  Map<String, dynamic>? tracking;
  int? status;
  int? priority;
  int? impressions;
  int? clicks;
  int? conversionCount;
  int? sentCount;
  int? parentId;
  String? abVariant;

  SmartAdModel({
    this.id,
    this.title,
    this.subTitle,
    this.image,
    this.imageUrl,
    this.img,
    this.picture,
    this.photo,
    this.videoUrl,
    this.adType,
    this.placement,
    this.buttonText,
    this.actionType,
    this.actionData,
    this.displaySettings,
    this.deepLink,
    this.createdAt,
    this.tracking,
    this.status,
    this.priority,
    this.impressions,
    this.clicks,
    this.conversionCount,
    this.sentCount,
    this.parentId,
    this.abVariant,
  });

  factory SmartAdModel.fromJson(Map<String, dynamic> json) {
    return SmartAdModel(
      id: json['id'] != null ? int.tryParse(json['id'].toString()) : null,
      title: json['title']?.toString(),
      subTitle: json['sub_title']?.toString(),
      image: json['image']?.toString(),
      imageUrl: json['image_url']?.toString(),
      img: json['img']?.toString(),
      picture: json['picture']?.toString(),
      photo: json['photo']?.toString(),
      videoUrl: json['video_url']?.toString(),
      adType: json['ad_type']?.toString(),
      placement: json['placement']?.toString(),
      buttonText: json['display_settings'] != null ? (json['display_settings']['button_text']?.toString()) : null,
      actionType: json['action_type']?.toString(),
      actionData: json['action_data'] != null ? ActionData.fromJson(json['action_data']) : null,
      displaySettings: json['display_settings'] != null ? Map<String, dynamic>.from(json['display_settings']) : null,
      deepLink: json['deep_link']?.toString(),
      createdAt: DateTime.tryParse(json['created_at']?.toString() ?? ''),
      tracking: json['tracking'] != null ? Map<String, dynamic>.from(json['tracking']) : null,
      status: json['status'] != null ? int.tryParse(json['status'].toString()) : null,
      priority: json['priority'] != null ? int.tryParse(json['priority'].toString()) : null,
      impressions: json['impressions'] != null ? int.tryParse(json['impressions'].toString()) : null,
      clicks: json['clicks'] != null ? int.tryParse(json['clicks'].toString()) : null,
      conversionCount: json['conversion_count'] != null ? int.tryParse(json['conversion_count'].toString()) : null,
      sentCount: json['sent_count'] != null ? int.tryParse(json['sent_count'].toString()) : null,
      parentId: json['parent_id'] != null ? int.tryParse(json['parent_id'].toString()) : null,
      abVariant: json['ab_variant']?.toString(),
    );
  }
}
