import 'package:flutter/material.dart';
import 'package:video_player/video_player.dart';
import 'package:chewie/chewie.dart';

class AdCardVideo extends StatefulWidget {
  final String? videoUrl;
  const AdCardVideo({this.videoUrl, Key? key}): super(key: key);
  @override
  State<AdCardVideo> createState() => _AdCardVideoState();
}

class _AdCardVideoState extends State<AdCardVideo> {
  VideoPlayerController? _controller;
  ChewieController? _chewie;

  @override
  void initState() {
    super.initState();
    if (widget.videoUrl != null) {
      _controller = VideoPlayerController.network(widget.videoUrl!);
      _chewie = ChewieController(videoPlayerController: _controller!, autoPlay: false, looping: false);
      _controller!.initialize().then((_) => setState((){}));
    }
  }

  @override
  void dispose() { _chewie?.dispose(); _controller?.dispose(); super.dispose(); }

  @override
  Widget build(BuildContext context) {
    if (_chewie == null) return SizedBox.shrink();
    return Chewie(controller: _chewie!);
  }
}
