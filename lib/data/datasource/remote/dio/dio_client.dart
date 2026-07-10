*** Begin Patch
*** Update File: lib/data/datasource/remote/dio/dio_client.dart
@@
     dio!.interceptors.add(loggingInterceptor);
+    // Add interceptor to include SmartAds-specific headers for endpoints that contain '/smartads'
+    dio!.interceptors.add(InterceptorsWrapper(
+      onRequest: (options, handler) async {
+        try {
+          final uri = options.path;
+          if (uri.contains('/smartads')) {
+            options.headers['User-Platform'] = 'all';
+            options.headers['Accept-Language'] = 'ar';
+            final userId = sharedPreferences.getString(AppConstants.userId) ?? '';
+            if (userId.isNotEmpty) options.headers['User-Id'] = userId;
+          }
+        } catch (_) {}
+        return handler.next(options);
+      },
+    ));
*** End Patch
