import 'package:flutter/material.dart';

class SmartAdDetailScreen extends StatelessWidget {
  const SmartAdDetailScreen({Key? key}): super(key:key);
  @override
  Widget build(BuildContext context) {
    return Scaffold(appBar: AppBar(title: Text('Ad Detail')), body: Center(child: Text('Detail with tabs (Notifications, Schedule, Analytics)')));
  }
}
