diff --git a/lib/helper/route_healper.dart b/lib/helper/route_healper.dart
index 0d63cb9..0000000 100644
--- a/lib/helper/route_healper.dart
+++ b/lib/helper/route_healper.dart
@@
@@
   static const String smartAdsNotificationScreen = '/smartads-notifications';
+  static const String smartAdFeed = '/smartads/feed';
+  static const String smartAdDetail = '/smartads/detail';
+  static const String smartAdForm = '/smartads/form';
+  static const String adminDashboard = '/smartads/admin';
+  static const String doseReminder = '/smartads/dose-reminder';
@@
       GoRoute(path: onboardingScreen, builder: (context, state) {
@@
       }),
+      GoRoute(path: smartAdFeed, builder: (context, state) => SmartAdFeedScreen()),
+      GoRoute(path: smartAdDetail, builder: (context, state) => SmartAdDetailScreen()),
+      GoRoute(path: smartAdForm, builder: (context, state) => SmartAdFormScreen()),
+      GoRoute(path: adminDashboard, builder: (context, state) => AdminDashboardScreen()),
+      GoRoute(path: doseReminder, builder: (context, state) => DoseReminderScreen()),
@@
