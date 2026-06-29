import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:flutter_sixvalley_ecommerce/common/basewidget/custom_app_bar_widget.dart';
import 'package:flutter_sixvalley_ecommerce/common/basewidget/not_loggedin_widget.dart';
import 'package:flutter_sixvalley_ecommerce/features/auth/controllers/auth_controller.dart';
import 'package:flutter_sixvalley_ecommerce/features/profile/controllers/profile_contrroller.dart';
import 'package:flutter_sixvalley_ecommerce/features/smartads/controllers/ad_controller.dart';
import 'package:flutter_sixvalley_ecommerce/features/smartads/domain/models/smart_ad_notification_model.dart';
import 'package:flutter_sixvalley_ecommerce/helper/date_converter.dart';
import 'package:flutter_sixvalley_ecommerce/helper/route_healper.dart';
import 'package:flutter_sixvalley_ecommerce/localization/language_constrants.dart';
import 'package:provider/provider.dart';

class SmartAdNotificationScreen extends StatefulWidget {
  const SmartAdNotificationScreen({super.key});

  @override
  State<SmartAdNotificationScreen> createState() => _SmartAdNotificationScreenState();
}

class _SmartAdNotificationScreenState extends State<SmartAdNotificationScreen> {
  @override
  void initState() {
    super.initState();
    _loadData();
  }

  void _loadData() {
    final authController = Provider.of<AuthController>(context, listen: false);
    if (authController.isLoggedIn()) {
      final profileController = Provider.of<ProfileController>(context, listen: false);
      int? userId = int.tryParse(profileController.userID);
      Provider.of<AdController>(context, listen: false).getNotifications(userId);
    }
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final isLoggedIn = Provider.of<AuthController>(context, listen: false).isLoggedIn();

    return Scaffold(
      appBar: CustomAppBar(
        title: getTranslated('notification_center', context) ?? "مركز الإشعارات",
        onBackPressed: () => Navigator.of(context).pop(),
      ),
      body: !isLoggedIn
          ? NotLoggedInWidget(
              fromPage: RouterHelper.dashboardScreen,
              onLoginSuccess: () {
                _loadData();
                setState(() {});
              },
            )
          : Consumer<AdController>(
              builder: (context, adController, child) {
                if (adController.isNotificationLoading) {
                  return const Center(child: CircularProgressIndicator());
                }

                if (adController.notifications.isEmpty) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(Icons.notifications_none_outlined, size: 64, color: theme.hintColor.withOpacity(0.5)),
                        const SizedBox(height: 16),
                        Text(
                          getTranslated('no_notification', context) ?? "لا توجد إشعارات حالياً",
                          style: TextStyle(color: theme.hintColor, fontSize: 16),
                        ),
                      ],
                    ),
                  );
                }

                return RefreshIndicator(
                  onRefresh: () async => _loadData(),
                  child: ListView.separated(
                    padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                    itemCount: adController.notifications.length,
                    separatorBuilder: (context, index) => const SizedBox(height: 10),
                    itemBuilder: (context, index) => _buildNotificationItem(adController.notifications[index], adController),
                  ),
                );
              },
            ),
    );
  }

  Widget _buildNotificationItem(SmartAdNotificationModel notification, AdController adController) {
    final theme = Theme.of(context);
    final isRead = notification.isRead ?? false;

    return Container(
      decoration: BoxDecoration(
        color: isRead ? theme.cardColor : theme.primaryColor.withOpacity(0.05),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(
          color: isRead ? theme.dividerColor.withOpacity(0.3) : theme.primaryColor.withOpacity(0.15),
          width: 1,
        ),
      ),
      child: InkWell(
        onTap: () {
          if (notification.id != null && !isRead) {
            adController.markNotificationAsRead(notification.id!);
          }
          // هنا يمكنك إضافة منطق SmartActionHelper.performAction إذا كان هناك payload
        },
        borderRadius: BorderRadius.circular(12),
        child: Padding(
          padding: const EdgeInsets.all(12),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              ClipRRect(
                borderRadius: BorderRadius.circular(8),
                child: notification.image != null && notification.image!.isNotEmpty
                    ? CachedNetworkImage(
                        imageUrl: notification.image!,
                        height: 50, width: 50, fit: BoxFit.cover,
                        errorWidget: (context, url, error) => _buildPlaceholderIcon(),
                      )
                    : _buildPlaceholderIcon(),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Expanded(
                          child: Text(
                            notification.title ?? '',
                            style: TextStyle(fontWeight: isRead ? FontWeight.normal : FontWeight.bold, fontSize: 14),
                            maxLines: 1, overflow: TextOverflow.ellipsis,
                          ),
                        ),
                        if (!isRead) Container(height: 8, width: 8, decoration: BoxDecoration(color: theme.primaryColor, shape: BoxShape.circle)),
                      ],
                    ),
                    const SizedBox(height: 4),
                    Text(
                      notification.body ?? '',
                      style: TextStyle(fontSize: 12, color: theme.hintColor),
                      maxLines: 2, overflow: TextOverflow.ellipsis,
                    ),
                    const SizedBox(height: 6),
                    if (notification.createdAt != null)
                      Text(
                        DateConverter.localDateToIsoStringAMPM(notification.createdAt!),
                        style: TextStyle(fontSize: 10, color: theme.hintColor),
                      ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildPlaceholderIcon() {
    final theme = Theme.of(context);
    return Container(
      height: 50, width: 50, color: theme.primaryColor.withOpacity(0.1),
      child: Icon(Icons.notifications_active_outlined, color: theme.primaryColor, size: 24),
    );
  }
}
