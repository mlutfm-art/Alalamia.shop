
import 'package:flutter_sixvalley_ecommerce/data/model/image_full_url.dart';
import 'package:flutter_sixvalley_ecommerce/features/shop/domain/models/seller_model.dart';

class OrderModel {
  int? totalSize;
  String? limit;
  String? offset;
  List<Orders>? orders;

  OrderModel({this.totalSize, this.limit, this.offset, this.orders});

  OrderModel.fromJson(Map<String, dynamic> json) {
    totalSize = json['total_size'];
    limit = json['limit'];
    offset = json['offset'];
    if (json['orders'] != null) {
      orders = <Orders>[];
      json['orders'].forEach((v) {
        orders!.add(Orders.fromJson(v));
      });
    }
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['total_size'] = totalSize;
    data['limit'] = limit;
    data['offset'] = offset;
    if (orders != null) {
      data['orders'] = orders!.map((v) => v.toJson()).toList();
    }
    return data;
  }
}

class Orders {
  int? id;
  int? customerId;
  int? isGuest;
  String? customerType;
  String? paymentStatus;
  String? orderStatus;
  String? paymentMethod;
  String? transactionRef;
  String? paymentBy;
  String? paymentNote;
  double? orderAmount;
  double? paidAmount;
  double? adminCommission;
  String? cause;
  String? createdAt;
  String? updatedAt;
  double? discountAmount;
  String? discountType;
  String? couponCode;
  String? couponDiscountBearer;
  int? shippingMethodId;
  double? shippingCost;
  bool? isShippingFree;
  String? orderGroupId;
  String? verificationCode;
  bool? verificationStatus;
  int? sellerId;
  String? sellerIs;
  ShippingAddressData? shippingAddressData;
  int? deliveryManId;
  double? deliverymanCharge;
  String? expectedDeliveryDate;
  String? deliverymanAssignedAt;
  String? orderNote;
  int? billingAddress;
  BillingAddressData? billingAddressData;
  String? orderType;
  double? extraDiscount;
  String? extraDiscountType;
  String? freeDeliveryBearer;
  String? shippingType;
  String? deliveryType;
  String? deliveryServiceName;
  String? thirdPartyDeliveryTrackingId;
  int? orderDetailsCount;
  List<Details>? details;
  DeliveryMan? deliveryMan;
  Seller? seller;
  double? bringChangeAmount;
  String? bringChangeAmountCurrency;
  double? totalTaxAmount;
  String? taxModel;
  String? thumbnail;
  ImageFullUrl? thumbnailFullUrl;


  Orders(
      {this.id,
        this.customerId,
        this.isGuest,
        this.customerType,
        this.paymentStatus,
        this.orderStatus,
        this.paymentMethod,
        this.transactionRef,
        this.paymentBy,
        this.paymentNote,
        this.orderAmount,
        this.paidAmount,
        this.adminCommission,
        this.cause,
        this.createdAt,
        this.updatedAt,
        this.discountAmount,
        this.discountType,
        this.couponCode,
        this.couponDiscountBearer,
        this.shippingMethodId,
        this.shippingCost,
        this.isShippingFree,
        this.orderGroupId,
        this.verificationCode,
        this.verificationStatus,
        this.sellerId,
        this.sellerIs,
        this.shippingAddressData,
        this.deliveryManId,
        this.deliverymanCharge,
        this.expectedDeliveryDate,
        this.orderNote,
        this.billingAddress,
        this.billingAddressData,
        this.orderType,
        this.extraDiscount,
        this.extraDiscountType,
        this.freeDeliveryBearer,
        this.shippingType,
        this.deliveryType,
        this.deliveryServiceName,
        this.thirdPartyDeliveryTrackingId,
        this.orderDetailsCount,
        this.details,
        this.deliveryMan,
        this.seller,
        this.bringChangeAmount,
        this.bringChangeAmountCurrency,
        this.deliverymanAssignedAt,
        this.totalTaxAmount,
        this.taxModel,
        this.thumbnail,
        this.thumbnailFullUrl
      });

  Orders.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    customerId = json['customer_id'];
    if(json['temporary_close'] != null){
      isGuest = int.tryParse(json['temporary_close'].toString());
    }else{
      isGuest = 0;
    }

    customerType = json['customer_type'];
    paymentStatus = json['payment_status'];
    orderStatus = json['order_status'];
    paymentMethod = json['payment_method'];
    transactionRef = json['transaction_ref'];
    paymentBy = json['payment_by'];
    paymentNote = json['payment_note'];
    orderAmount = json['order_amount']?.toDouble();
    if (json['paid_amount'] != null) {
      paidAmount = json['paid_amount'].toDouble();
    }else{
      paidAmount = 0;
    }
    adminCommission =  double.tryParse(json['admin_commission'].toString());
    cause = json['cause'];
    createdAt = json['created_at'];
    updatedAt = json['updated_at'];
    discountAmount = json['discount_amount']?.toDouble();
    discountType = json['discount_type'];
    couponCode = json['coupon_code'];
    couponDiscountBearer = json['coupon_discount_bearer'];
    shippingMethodId = json['shipping_method_id'];
    shippingCost = json['shipping_cost']?.toDouble();
    isShippingFree = json['is_shipping_free']??false;
    orderGroupId = json['order_group_id'];
    verificationCode = json['verification_code'];
    verificationStatus = json['verification_status']??false;
    sellerId = json['seller_id'];
    sellerIs = json['seller_is'];
    shippingAddressData = json['shipping_address_data'] != null ? ShippingAddressData.fromJson(json['shipping_address_data']) : null;
    deliveryManId = json['delivery_man_id'];
    if(json['deliveryman_charge'] != null){
      deliverymanCharge = double.parse(json['deliveryman_charge'].toString());
    }else{
      deliverymanCharge = 0;
    }

    expectedDeliveryDate = json['expected_delivery_date'];
    deliverymanAssignedAt = json['deliveryman_assigned_at'];
    orderNote = json['order_note'];
    billingAddress = json['billing_address'];
    billingAddressData = json['billing_address_data'] != null ? BillingAddressData.fromJson(json['billing_address_data']) : null;
    orderType = json['order_type'];
    extraDiscount = json['extra_discount']?.toDouble();
    extraDiscountType = json['extra_discount_type'];
    freeDeliveryBearer = json['free_delivery_bearer'];
    shippingType = json['shipping_type'];
    deliveryType = json['delivery_type'];
    deliveryServiceName = json['delivery_service_name'];
    thirdPartyDeliveryTrackingId = json['third_party_delivery_tracking_id'];
    if(json['order_details_count'] != null){
      orderDetailsCount = int.tryParse(json['order_details_count'].toString());
    }else{
      orderDetailsCount = 0;
    }

    if (json['details'] != null) {
      details = <Details>[];
      json['details'].forEach((v) {
        details!.add(Details.fromJson(v));
      });
    }
    deliveryMan = json['delivery_man'] != null ? DeliveryMan.fromJson(json['delivery_man']) : null;
    seller = json['seller'] != null ? Seller.fromJson(json['seller']) : null;
    bringChangeAmount = double.tryParse('${json['bring_change_amount']}');
    bringChangeAmountCurrency = json['bring_change_amount_currency'];
    totalTaxAmount = json['total_tax_amount'] != null ? double.tryParse('${json['total_tax_amount']}') : null;
    taxModel = json['tax_model'];
    thumbnail = json['thumbnail'];
    thumbnailFullUrl = json['thumbnail_full_url'] != null
        ? ImageFullUrl.fromJson(json['thumbnail_full_url'])
        : null;
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['id'] = id;
    data['customer_id'] = customerId;
    data['temporary_close'] = isGuest;
    data['customer_type'] = customerType;
    data['payment_status'] = paymentStatus;
    data['order_status'] = orderStatus;
    data['payment_method'] = paymentMethod;
    data['transaction_ref'] = transactionRef;
    data['payment_by'] = paymentBy;
    data['payment_note'] = paymentNote;
    data['order_amount'] = orderAmount;
    data['paid_amount'] = paidAmount;
    data['admin_commission'] = adminCommission;
    data['cause'] = cause;
    data['created_at'] = createdAt;
    data['updated_at'] = updatedAt;
    data['discount_amount'] = discountAmount;
    data['discount_type'] = discountType;
    data['coupon_code'] = couponCode;
    data['coupon_discount_bearer'] = couponDiscountBearer;
    data['shipping_method_id'] = shippingMethodId;
    data['shipping_cost'] = shippingCost;
    data['is_shipping_free'] = isShippingFree;
    data['order_group_id'] = orderGroupId;
    data['verification_code'] = verificationCode;
    data['verification_status'] = verificationStatus;
    data['seller_id'] = sellerId;
    data['seller_is'] = sellerIs;
    if (shippingAddressData != null) {
      data['shipping_address_data'] = shippingAddressData!.toJson();
    }
    data['delivery_man_id'] = deliveryManId;
    data['deliveryman_charge'] = deliverymanCharge;
    data['expected_delivery_date'] = expectedDeliveryDate;
    data['deliveryman_assigned_at'] = deliverymanAssignedAt;
    data['order_note'] = orderNote;
    data['billing_address'] = billingAddress;
    if (billingAddressData != null) {
      data['billing_address_data'] = billingAddressData!.toJson();
    }
    data['order_type'] = orderType;
    data['extra_discount'] = extraDiscount;
    data['extra_discount_type'] = extraDiscountType;
    data['free_delivery_bearer'] = freeDeliveryBearer;
    data['shipping_type'] = shippingType;
    data['delivery_type'] = deliveryType;
    data['delivery_service_name'] = deliveryServiceName;
    data['third_party_delivery_tracking_id'] = thirdPartyDeliveryTrackingId;
    data['order_details_count'] = orderDetailsCount;
    if (details != null) {
      data['details'] = details!.map((v) => v.toJson()).toList();
    }
    if (deliveryMan != null) {
      data['delivery_man'] = deliveryMan!.toJson();
    }
    if (seller != null) {
      data['seller'] = seller!.toJson();
    }
    data['bring_change_amount'] = bringChangeAmount;
    data['bring_change_amount_currency'] = bringChangeAmountCurrency;
    data['total_tax_amount'] = totalTaxAmount;
    data['tax_model'] = taxModel;
    data['thumbnail'] = thumbnail;
    if (thumbnailFullUrl != null) {
      data['thumbnail_full_url'] = thumbnailFullUrl!.toJson();
    }
    return data;
  }
}


class BillingAddressData {
  int? id;
  String? contactPersonName;
  String? addressType;
  String? address;
  String? city;
  String? zip;
  String? phone;
  String? createdAt;
  String? updatedAt;
  String? country;
  String? latitude;
  String? longitude;

  BillingAddressData(
      {this.id,
        this.contactPersonName,
        this.addressType,
        this.address,
        this.city,
        this.zip,
        this.phone,
        this.createdAt,
        this.updatedAt,
        this.country,
        this.latitude,
        this.longitude});

  BillingAddressData.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    contactPersonName = json['contact_person_name'];
    addressType = json['address_type'];
    address = json['address'];
    city = json['city'];
    zip = json['zip'];
    phone = json['phone'];
    createdAt = json['created_at'];
    updatedAt = json['updated_at'];
    country = json['country'];
    latitude = json['latitude'];
    longitude = json['longitude'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['id'] = id;
    data['contact_person_name'] = contactPersonName;
    data['address_type'] = addressType;
    data['address'] = address;
    data['city'] = city;
    data['zip'] = zip;
    data['phone'] = phone;
    data['created_at'] = createdAt;
    data['updated_at'] = updatedAt;
    data['country'] = country;
    data['latitude'] = latitude;
    data['longitude'] = longitude;
    return data;
  }
}

class ShippingAddressData {
  int? _id;
  String? _contactPersonName;
  String? _addressType;
  String? _address;
  String? _city;
  String? _zip;
  String? _phone;
  String? _createdAt;
  String? _updatedAt;
  String? _country;

  ShippingAddressData(
      {int? id,
        String? contactPersonName,
        String? addressType,
        String? address,
        String? city,
        String? zip,
        String? phone,
        String? createdAt,
        String? updatedAt,
        String? country}) {
    if (id != null) {
      _id = id;
    }
    if (contactPersonName != null) {
      _contactPersonName = contactPersonName;
    }
    if (addressType != null) {
      _addressType = addressType;
    }
    if (address != null) {
      _address = address;
    }
    if (city != null) {
      _city = city;
    }
    if (zip != null) {
      _zip = zip;
    }
    if (phone != null) {
      _phone = phone;
    }
    if (createdAt != null) {
      _createdAt = createdAt;
    }
    if (updatedAt != null) {
      _updatedAt = updatedAt;
    }
    if (country != null) {
      _country = country;
    }
  }

  int? get id => _id;
  String? get contactPersonName => _contactPersonName;
  String? get addressType => _addressType;
  String? get address => _address;
  String? get city => _city;
  String? get zip => _zip;
  String? get phone => _phone;
  String? get createdAt => _createdAt;
  String? get updatedAt => _updatedAt;
  String? get country => _country;


  ShippingAddressData.fromJson(Map<String, dynamic> json) {
    _id = json['id'];
    _contactPersonName = json['contact_person_name'];
    _addressType = json['address_type'];
    _address = json['address'];
    _city = json['city'];
    _zip = json['zip'];
    _phone = json['phone'];
    _createdAt = json['created_at'];
    _updatedAt = json['updated_at'];
    _country = json['country'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['id'] = _id;
    data['contact_person_name'] = _contactPersonName;
    data['address_type'] = _addressType;
    data['address'] = _address;
    data['city'] = _city;
    data['zip'] = _zip;
    data['phone'] = _phone;
    data['created_at'] = _createdAt;
    data['updated_at'] = _updatedAt;
    data['country'] = _country;
    return data;
  }
}

class DeliveryMan {
  int? _id;
  String? _fName;
  String? _lName;
  String? _phone;
  String? _email;
  String? _image;
  ImageFullUrl? _imageFullUrl;
  DeliveryMan(
      {
        int? id,
        String? fName,
        String? lName,
        String? phone,
        String? email,
        String? image,
        ImageFullUrl? imageFullUrl
      }) {

    if (id != null) {
      _id = id;
    }
    if (fName != null) {
      _fName = fName;
    }
    if (lName != null) {
      _lName = lName;
    }
    if (phone != null) {
      _phone = phone;
    }
    if (email != null) {
      _email = email;
    }

    if (image != null) {
      _image = image;
    }
    if(imageFullUrl != null) {
      _imageFullUrl = imageFullUrl;
    }
  }


  int? get id => _id;
  String? get fName => _fName;
  String? get lName => _lName;
  String? get phone => _phone;
  String? get email => _email;
  String? get image => _image;
  ImageFullUrl? get imageFullUrl => _imageFullUrl;

  DeliveryMan.fromJson(Map<String, dynamic> json) {

    _id = json['id'];
    _fName = json['f_name'];
    _lName = json['l_name'];
    _phone = json['phone'];
    _email = json['email'];
    _image = json['image'];
    _imageFullUrl = json['image_full_url'] != null
        ? ImageFullUrl.fromJson(json['image_full_url'])
        : null;
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};

    data['id'] = _id;
    data['f_name'] = _fName;
    data['l_name'] = _lName;
    data['phone'] = _phone;
    data['email'] = _email;
    data['image'] = _image;
    if (_imageFullUrl != null) {
      data['image_full_url'] = _imageFullUrl!.toJson();
    }
    return data;
  }
}



class Shop {
  String? image;
  String? name;
  Shop(
      {this.image, this.name});

  Shop.fromJson(Map<String, dynamic> json) {
    image = json['image'];
    name = json['name'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['image'] = image;
    data['name'] = name;
    return data;
  }
}


class Details {
  Product? product;
  int? qty;
  double? price;
  double? tax;
  double? discount;
  String? thumbnail;
  ImageFullUrl? thumbnailFullUrl;

  Details(
      {
        this.product,
        this.qty,
        this.price,
        this.tax,
        this.discount,
        this.thumbnail,
        this.thumbnailFullUrl
      });

  Details.fromJson(Map<String, dynamic> json) {
    product = json['product'] != null ? Product.fromJson(json['product']) : null;
    qty = json['qty'];
    price = json['price']?.toDouble();
    tax = json['tax']?.toDouble();
    discount = json['discount']?.toDouble();
    thumbnail = json['thumbnail'];
    thumbnailFullUrl = json['thumbnail_full_url'] != null
        ? ImageFullUrl.fromJson(json['thumbnail_full_url'])
        : null;
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    if (product != null) {
      data['product'] = product!.toJson();
    }
    data['qty'] = qty;
    data['price'] = price;
    data['tax'] = tax;
    data['discount'] = discount;
    data['thumbnail'] = thumbnail;
    if (thumbnailFullUrl != null) {
      data['thumbnail_full_url'] = thumbnailFullUrl!.toJson();
    }
    return data;
  }

}

class Product {
  String? thumbnail;
  String? productType;
  ImageFullUrl? thumbnailFullUrl;


  Product(
      {this.thumbnail, this.productType, this.thumbnailFullUrl});

  Product.fromJson(Map<String, dynamic> json) {
    thumbnail = json['thumbnail'];
    productType = json['product_type'];
    thumbnailFullUrl = json['thumbnail_full_url'] != null
        ? ImageFullUrl.fromJson(json['thumbnail_full_url'])
        : null;

  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['thumbnail'] = thumbnail;
    data['product_type'] = productType;
    if (thumbnailFullUrl != null) {
      data['thumbnail_full_url'] = thumbnailFullUrl!.toJson();
    }
    return data;
  }
}
