import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';

class SmartAdFormScreen extends StatefulWidget {
  const SmartAdFormScreen({Key? key}): super(key:key);
  @override
  State<SmartAdFormScreen> createState() => _SmartAdFormScreenState();
}

class _SmartAdFormScreenState extends State<SmartAdFormScreen> {
  final _formKey = GlobalKey<FormState>();
  XFile? _picked;
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('Create/Edit Ad')),
      body: Form(key:_formKey, child: ListView(padding: EdgeInsets.all(12), children: [
        TextFormField(decoration: InputDecoration(labelText: 'Title')),
        SizedBox(height:8),
        ElevatedButton(onPressed: () async { final p = await ImagePicker().pickImage(source: ImageSource.gallery); if (p!=null) setState(()=>_picked=p); }, child: Text('Pick Image')),
        SizedBox(height:12),
        ElevatedButton(onPressed: ()=> Navigator.pop(context), child: Text('Save'))
      ]))
    );
  }
}
