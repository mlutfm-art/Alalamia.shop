class NotificationBody {
  int? orderId;
  int? matchId;
  String? type;
  String? status;
  String? messageKey;
  String? title;
  String? productId;
  String? slug;
  String? image;

  NotificationBody({
    this.orderId,
    this.matchId,
    this.type,
    this.status,
    this.messageKey,
    this.title,
    this.productId,
    this.slug,
    this.image
  });

  NotificationBody.fromJson(Map<String, dynamic> json) {
    orderId = int.tryParse(json['order_id']?.toString() ?? '');
    matchId = int.tryParse(json['match_id']?.toString() ?? '');
    type = json['type'];
    messageKey = json['message_key'];
    title = json['title'];
    productId = json['product_id'];
    slug = json['slug'];
    image = json['image'];
    status = json['status'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['order_id'] = orderId;
    data['match_id'] = matchId;
    data['type'] = type;
    data['message_key'] = messageKey;
    data['title'] = title;
    data['product_id'] = productId;
    data['slug'] = slug;
    data['image'] = image;
    data['status'] = status;
    return data;
  }
}
