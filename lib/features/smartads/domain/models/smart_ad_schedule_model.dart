class SmartAdScheduleModel {
  int? id;
  int? adId;
  String? title;
  String? body;
  String? image;
  String? targetType;
  String? targetValue;
  DateTime? scheduledAt;
  int? status;

  SmartAdScheduleModel({this.id, this.adId, this.title, this.body, this.image, this.targetType, this.targetValue, this.scheduledAt, this.status});

  factory SmartAdScheduleModel.fromJson(Map<String, dynamic> json) {
    return SmartAdScheduleModel(
      id: json['id'] != null ? int.tryParse(json['id'].toString()) : null,
      adId: json['ad_id'] != null ? int.tryParse(json['ad_id'].toString()) : null,
      title: json['title']?.toString(),
      body: json['body']?.toString(),
      image: json['image']?.toString(),
      targetType: json['target_type']?.toString(),
      targetValue: json['target_value']?.toString(),
      scheduledAt: DateTime.tryParse(json['scheduled_at']?.toString() ?? ''),
      status: json['status'] != null ? int.tryParse(json['status'].toString()) : null,
    );
  }
}
