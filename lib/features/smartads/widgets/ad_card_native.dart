import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';

class AdCardNative extends StatelessWidget {
  final String? title;
  final String? body;
  final String? image;
  const AdCardNative({this.title, this.body, this.image, Key? key}): super(key: key);

  @override
  Widget build(BuildContext context) {
    return Card(
      child: Padding(
        padding: EdgeInsets.all(8),
        child: Row(
          children: [
            if (image != null) CachedNetworkImage(imageUrl: image!, width: 80, height: 80, fit: BoxFit.cover),
            SizedBox(width: 8),
            Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [ if (title!=null) Text(title!, style: TextStyle(fontWeight: FontWeight.bold)), if (body!=null) Text(body!) ])),
          ],
        ),
      ),
    );
  }
}
