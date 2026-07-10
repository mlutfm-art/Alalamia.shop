class ActionData {
  String? type;
  Map<String, dynamic>? payload;
  Map<String, dynamic>? feedback;

  ActionData({this.type, this.payload, this.feedback});

  factory ActionData.fromJson(Map<String, dynamic> json) {
    return ActionData(
      type: json['type']?.toString(),
      payload: json['payload'] != null ? Map<String, dynamic>.from(json['payload']) : null,
      feedback: json['feedback'] != null ? Map<String, dynamic>.from(json['feedback']) : null,
    );
  }
}
