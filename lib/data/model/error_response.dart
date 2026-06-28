class ErrorResponse {
  List<Errors>? errors;

  ErrorResponse({this.errors});

  ErrorResponse.fromJson(Map<String, dynamic> json) {
    if (json['errors'] != null) {
      errors = <Errors>[];
      if (json['errors'] is List) {
        for (var v in json['errors']) {
          if (v is Map<String, dynamic>) {
            errors!.add(Errors.fromJson(v));
          }
        }
      } else if (json['errors'] is Map) {
        // معالجة الخطأ إذا جاء ككائن (Map) لتجنب الانهيار
        Map<String, dynamic> errorMap = Map<String, dynamic>.from(json['errors']);
        errorMap.forEach((key, value) {
          errors!.add(Errors(code: key, message: value.toString()));
        });
      }
    } else if (json['message'] != null) {
      errors = <Errors>[];
      errors!.add(Errors(message: json['message'].toString()));
    }
  }
}

class Errors {
  String? code;
  String? message;

  Errors({this.code, this.message});

  Errors.fromJson(Map<String, dynamic> json) {
    code = json['code']?.toString();
    message = json['message'];
  }
}
