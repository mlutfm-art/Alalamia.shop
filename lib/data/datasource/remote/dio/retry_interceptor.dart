import 'package:dio/dio.dart';
import 'package:flutter_sixvalley_ecommerce/features/connectivity/controllers/connectivity_controller.dart';

class RetryInterceptor extends Interceptor {
  final ConnectivityController connectivityController;

  RetryInterceptor({required this.connectivityController});

  @override
  Future onError(DioException err, ErrorInterceptorHandler handler) async {
    if (_shouldRetry(err)) {
      connectivityController.addFailedRequest(err.requestOptions);
    }
    return super.onError(err, handler);
  }

  bool _shouldRetry(DioException err) {
    return err.type == DioExceptionType.connectionError ||
        err.type == DioExceptionType.connectionTimeout ||
        err.type == DioExceptionType.sendTimeout ||
        err.type == DioExceptionType.receiveTimeout ||
        err.error is dynamic; // Capture other network related errors
  }
}
