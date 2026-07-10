import 'package:flutter/material.dart';

class DoseReminderScreen extends StatelessWidget {
  const DoseReminderScreen({Key? key}): super(key:key);
  @override
  Widget build(BuildContext context) {
    return Scaffold(appBar: AppBar(title: Text('Dose Reminder')),
    body: Center(child: Text('List of doses with confirm/snooze')));
  }
}
