import 'package:flutter/material.dart';

class AdminDashboardScreen extends StatelessWidget {
  const AdminDashboardScreen({Key? key}): super(key:key);
  @override
  Widget build(BuildContext context) {
    return Scaffold(appBar: AppBar(title: Text('SmartAds Admin')),
    body: Center(child: Text('Admin stats and quick actions')));
  }
}
