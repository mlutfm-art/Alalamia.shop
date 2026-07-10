class DeviceTokenModel {
  int? id;
  int? tokenableId;
  String? tokenableType;
  String? token;
  String? deviceType;
  String? browser;
  DateTime? lastUsedAt;

  DeviceTokenModel({this.id, this.tokenableId, this.tokenableType, this.token, this.deviceType, this.browser, this.lastUsedAt});

  factory DeviceTokenModel.fromJson(Map<String, dynamic> json) {
    return DeviceTokenModel(
      id: json['id'] != null ? int.tryParse(json['id'].toString()) : null,
      tokenableId: json['tokenable_id'] != null ? int.tryParse(json['tokenable_id'].toString()) : null,
      tokenableType: json['tokenable_type']?.toString(),
      token: json['token']?.toString(),
      deviceType: json['device_type']?.toString(),
      browser: json['browser']?.toString(),
      lastUsedAt: DateTime.tryParse(json['last_used_at']?.toString() ?? ''),
    );
  }
}
