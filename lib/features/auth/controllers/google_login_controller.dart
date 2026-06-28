import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter_sixvalley_ecommerce/utill/app_constants.dart';
import 'package:google_sign_in/google_sign_in.dart';

class GoogleSignInController with ChangeNotifier {
  final GoogleSignIn _googleSignIn = GoogleSignIn.instance;
  GoogleSignInAccount? googleAccount;
  GoogleSignInClientAuthorization? auth;
  String errorMessage = '';

  GoogleSignInController() {
    _initialize();
  }

  Future<void> _initialize() async {
    await _googleSignIn.initialize(
      serverClientId: AppConstants.googleServerClientId,
    );

    // Listen to authentication events like official example
    _googleSignIn.authenticationEvents.listen(_handleAuthenticationEvent);
  }

  Future<void> _handleAuthenticationEvent(GoogleSignInAuthenticationEvent event) async {
    if (event is GoogleSignInAuthenticationEventSignIn) {
      googleAccount = event.user;
    } else if (event is GoogleSignInAuthenticationEventSignOut) {
      googleAccount = null;
    }

    if (googleAccount != null) {
      const List<String> scopes = <String>['email'];
      auth = await googleAccount?.authorizationClient.authorizationForScopes(scopes);
    } else {
      auth = null;
    }


    notifyListeners();
  }

  Future<void> login() async {
    try {
      errorMessage = '';

      final completer = Completer<void>();

      // Temporary subscription to wait for event
      final sub = _googleSignIn.authenticationEvents.listen((event) async {
        await _handleAuthenticationEvent(event);
        if (!completer.isCompleted) completer.complete();
      });

      try {
        await _googleSignIn.authenticate();
        await completer.future.timeout(const Duration(seconds: 30)); 
      } on TimeoutException {
        if (!completer.isCompleted) completer.complete();
      } finally {
        await sub.cancel();
      }
    } catch (e) {
      if (e is GoogleSignInException && e.code == GoogleSignInExceptionCode.canceled) {
        errorMessage = 'Sign in canceled';
      } else {
        errorMessage = e.toString();
        rethrow;
      }
      notifyListeners();
    }
  }

  Future<void> logout() async {
    await _googleSignIn.disconnect();
  }
}
