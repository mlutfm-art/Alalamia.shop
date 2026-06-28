import 'dart:convert';
import 'dart:io';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:flutter_sixvalley_ecommerce/features/chat/controllers/chat_controller.dart';
import 'package:flutter_sixvalley_ecommerce/features/notification/controllers/notification_controller.dart';
import 'package:flutter_sixvalley_ecommerce/helper/route_healper.dart';
import 'package:flutter_sixvalley_ecommerce/main.dart';
import 'package:flutter_sixvalley_ecommerce/push_notification/models/notification_body.dart';
import 'package:flutter_sixvalley_ecommerce/utill/app_constants.dart';
import 'package:path_provider/path_provider.dart';
import 'package:http/http.dart' as http;
import 'package:provider/provider.dart';

class NotificationHelper {

  static Future<void> initialize(FlutterLocalNotificationsPlugin flutterLocalNotificationsPlugin) async {
    var androidInitialize = const AndroidInitializationSettings('notification_icon');
    var iOSInitialize = const DarwinInitializationSettings();
    var initializationsSettings = InitializationSettings(android: androidInitialize, iOS: iOSInitialize);
    
    flutterLocalNotificationsPlugin.initialize(initializationsSettings, onDidReceiveNotificationResponse: (NotificationResponse payload) async {
      try{
        NotificationBody payloadModel;
        if(payload.payload != null && payload.payload!.isNotEmpty) {
          payloadModel = NotificationBody.fromJson(jsonDecode(payload.payload!));
          _navigate(payloadModel);
        }
      }catch (e) {
        debugPrint("Notification Error: $e");
      }
      return;
    });

    FirebaseMessaging.onMessage.listen((RemoteMessage message) {
      showNotification(message, flutterLocalNotificationsPlugin, kIsWeb);
      if(message.data['type'] == 'chatting'){
        Provider.of<ChatController>(Get.context!, listen: false).getChatList(1, reload: false, userType: message.data['message_key'] == 'message_from_delivery_man' ? 0 : 1);
      }
      Provider.of<NotificationController>(Get.context!, listen: false).getNotificationList(1);
    });

    FirebaseMessaging.onMessageOpenedApp.listen((RemoteMessage message) {
      try{
        if(message.data.isNotEmpty) {
          NotificationBody notificationBody = convertNotification(message.data);
          _navigate(notificationBody);
        }
      }catch (e) {
        debugPrint("Notification Error: $e");
      }
    });
  }

  static void _navigate(NotificationBody payloadModel) {
    if (payloadModel.type == 'order') {
      RouterHelper.getOrderDetailsScreenRoute(orderId: payloadModel.orderId!, action: RouteAction.push);
    } else if(payloadModel.type == 'notification') {
      RouterHelper.getNotificationRoute(action: RouteAction.push);
    } else if(payloadModel.type == 'chatting') {
      Provider.of<ChatController>(Get.context!, listen: false).setUserTypeIndex(Get.context!, payloadModel.messageKey == 'message_from_delivery_man' ? 0 : 1);
      RouterHelper.getInboxScreenRoute(action: RouteAction.push, initIndex: payloadModel.messageKey == 'message_from_delivery_man' ? 0 : 1);
    } else if(payloadModel.type == 'prediction') {
      RouterHelper.getPredictionHubRoute(action: RouteAction.push, matchId: payloadModel.matchId);
    } else if(payloadModel.type == 'wallet') {
      RouterHelper.getWalletRoute(action: RouteAction.push, isBackButtonExist: true);
    } else if(payloadModel.type == 'product_restock_update') {
      RouterHelper.getProductDetailsRoute(action: RouteAction.push, productId: int.tryParse(payloadModel.productId ?? ''), slug: payloadModel.slug, isNotification: true);
    } else {
      RouterHelper.getNotificationRoute(action: RouteAction.push);
    }
  }

  static Future<void> showNotification(RemoteMessage message, FlutterLocalNotificationsPlugin flutterLocalNotificationsPlugin, bool data) async {
    String? title;
    String? body;
    String? orderID;
    String? image;
    NotificationBody notificationBody = convertNotification(message.data);
    
    if(data) {
      title = message.data['title']?.toString();
      body = message.data['body']?.toString();
      orderID = message.data['order_id']?.toString();
      image = (message.data['image'] != null && message.data['image'].isNotEmpty)
          ? message.data['image'].startsWith('http') ? message.data['image']
          : '${AppConstants.baseUrl}/storage/app/public/notification/${message.data['image']}' : null;
    }else {
      title = message.notification?.title;
      body = message.notification?.body;
      orderID = message.notification?.titleLocKey;
      if(Platform.isAndroid) {
        image = (message.notification?.android?.imageUrl != null && message.notification!.android!.imageUrl!.isNotEmpty)
            ? message.notification!.android!.imageUrl!.startsWith('http') ? message.notification!.android!.imageUrl
            : '${AppConstants.baseUrl}/storage/app/public/notification/${message.notification!.android!.imageUrl}' : null;
      }else if(Platform.isIOS) {
        image = (message.notification?.apple?.imageUrl != null && message.notification!.apple!.imageUrl!.isNotEmpty)
            ? message.notification!.apple!.imageUrl!.startsWith('http') ? message.notification!.apple!.imageUrl
            : '${AppConstants.baseUrl}/storage/app/public/notification/${message.notification!.apple!.imageUrl}' : null;
      }
    }

    if(image != null && image.isNotEmpty) {
      try{
        await showBigPictureNotificationHiddenLargeIcon(title, body, orderID, notificationBody, image, flutterLocalNotificationsPlugin);
      }catch(e) {
        await showTextNotification(title, body, orderID, notificationBody, flutterLocalNotificationsPlugin);
      }
    }else {
      await showTextNotification(title, body, orderID, notificationBody, flutterLocalNotificationsPlugin);
    }
  }

  static Future<void> showTextNotification(String? title, String? body, String? orderID, NotificationBody? notificationBody, FlutterLocalNotificationsPlugin flutterLocalNotificationsPlugin) async {
    const AndroidNotificationDetails androidPlatformChannelSpecifics = AndroidNotificationDetails(
      'sixvalley', 'sixvalley', playSound: true,
      importance: Importance.max, priority: Priority.high, icon: 'notification_icon',
    );
    const NotificationDetails platformChannelSpecifics = NotificationDetails(android: androidPlatformChannelSpecifics);
    await flutterLocalNotificationsPlugin.show(0, title, body, platformChannelSpecifics, payload: notificationBody != null ? jsonEncode(notificationBody.toJson()) : null);
  }

  static Future<void> showBigPictureNotificationHiddenLargeIcon(String? title, String? body, String? orderID, NotificationBody? notificationBody, String image, FlutterLocalNotificationsPlugin flutterLocalNotificationsPlugin) async {
    final String largeIconPath = await _downloadAndSaveFile(image, 'largeIcon');
    final String bigPicturePath = await _downloadAndSaveFile(image, 'bigPicture');
    final BigPictureStyleInformation bigPictureStyleInformation = BigPictureStyleInformation(
      FilePathAndroidBitmap(bigPicturePath), hideExpandedLargeIcon: true,
      contentTitle: title, htmlFormatContentTitle: true,
      summaryText: body, htmlFormatSummaryText: true,
    );
    final AndroidNotificationDetails androidPlatformChannelSpecifics = AndroidNotificationDetails(
      'sixvalley', 'sixvalley',
      largeIcon: FilePathAndroidBitmap(largeIconPath), priority: Priority.high, playSound: true,
      styleInformation: bigPictureStyleInformation, importance: Importance.max, icon: 'notification_icon',
    );
    final NotificationDetails platformChannelSpecifics = NotificationDetails(android: androidPlatformChannelSpecifics);
    await flutterLocalNotificationsPlugin.show(0, title, body, platformChannelSpecifics, payload: notificationBody != null ? jsonEncode(notificationBody.toJson()) : null);
  }

  static Future<String> _downloadAndSaveFile(String url, String fileName) async {
    final Directory directory = await getApplicationDocumentsDirectory();
    final String filePath = '${directory.path}/$fileName';
    final http.Response response = await http.get(Uri.parse(url));
    final File file = File(filePath);
    await file.writeAsBytes(response.bodyBytes);
    return filePath;
  }

  static NotificationBody convertNotification(Map<String, dynamic> data){
    if(data['type'] == 'notification') {
      return NotificationBody(type: 'notification');
    }else if(data['type'] == 'order') {
      return NotificationBody(type: 'order', orderId: int.tryParse(data['order_id']?.toString() ?? '') ?? 0);
    }else if(data['type'] == 'chatting') {
      return NotificationBody(type: 'chatting', messageKey: data['message_key']);
    }else if(data['type'] == 'prediction') {
      return NotificationBody(
        type: 'prediction', 
        matchId: int.tryParse(data['match_id']?.toString() ?? '')
      );
    }else if(data['type'] == 'wallet') {
      return NotificationBody(type: 'wallet');
    }else if(data['type'] == 'product_restock_update') {
      return NotificationBody(type: 'product_restock_update', productId: data['product_id'], slug: data['slug']);
    }else {
      return NotificationBody(type: 'notification');
    }
  }

}

Future<dynamic> myBackgroundMessageHandler(RemoteMessage message) async {
  debugPrint("onBackgroundMessage: ${message.data}");
}
