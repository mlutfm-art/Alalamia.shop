import 'dart:async';
import 'dart:io';
import 'package:connectivity_plus/connectivity_plus.dart';
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';

class ConnectivityController extends ChangeNotifier {
  final Connectivity _connectivity = Connectivity();
  bool _isConnected = true;
  bool get isConnected => _isConnected;

  // Queue for failed requests
  final List<RequestOptions> _failedRequests = [];
  final Dio _retryDio = Dio(); // Separate Dio instance for retries to avoid recursion

  ConnectivityController() {
    _checkConnectivity();
    _connectivity.onConnectivityChanged.listen((result) {
      _handleConnectivityChange(result);
    });
  }

  Future<void> _checkConnectivity() async {
    List<ConnectivityResult> result = await _connectivity.checkConnectivity();
    await _handleConnectivityChange(result);
  }

  Future<void> _handleConnectivityChange(List<ConnectivityResult> result) async {
    bool prevConnection = _isConnected;
    if (result.contains(ConnectivityResult.none)) {
      _isConnected = false;
    } else {
      // Real Internet Check
      _isConnected = await _hasRealInternet();
    }

    if (_isConnected && !prevConnection) {
      // Reconnected!
      _onReconnected();
    }
    notifyListeners();
  }

  Future<bool> _hasRealInternet() async {
    try {
      final result = await InternetAddress.lookup('google.com');
      return result.isNotEmpty && result[0].rawAddress.isNotEmpty;
    } on SocketException catch (_) {
      return false;
    }
  }

  void _onReconnected() {
    debugPrint("Internet Reconnected. Retrying failed requests: ${_failedRequests.length}");
    _retryFailedRequests();
  }

  void addFailedRequest(RequestOptions options) {
    // Check if request already in queue to prevent duplicates
    bool exists = _failedRequests.any((r) => 
      r.path == options.path && 
      r.method == options.method && 
      r.data.toString() == options.data.toString()
    );
    
    if (!exists) {
      _failedRequests.add(options);
      debugPrint("Request added to retry queue: ${options.path}");
    }
  }

  Future<void> _retryFailedRequests() async {
    if (_failedRequests.isEmpty) return;

    List<RequestOptions> queue = List.from(_failedRequests);
    _failedRequests.clear();

    for (var options in queue) {
      try {
        debugPrint("Retrying request: ${options.path}");
        // Note: You might need to refresh headers (like token) here if it's expired
        await _retryDio.request(
          options.path,
          data: options.data,
          queryParameters: options.queryParameters,
          options: Options(
            method: options.method,
            headers: options.headers,
            contentType: options.contentType,
          ),
        );
        debugPrint("Retry successful for: ${options.path}");
      } catch (e) {
        debugPrint("Retry failed for: ${options.path}. Adding back to queue.");
        _failedRequests.add(options);
      }
    }
    notifyListeners();
  }
}
