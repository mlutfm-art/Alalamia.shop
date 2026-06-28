import 'package:flutter/material.dart';
import 'package:flutter_sixvalley_ecommerce/features/prediction/controllers/prediction_controller.dart';
import 'package:flutter_sixvalley_ecommerce/features/prediction/widgets/prediction_match_card_widget.dart';
import 'package:flutter_sixvalley_ecommerce/theme/controllers/theme_controller.dart';
import 'package:flutter_sixvalley_ecommerce/utill/custom_themes.dart';
import 'package:flutter_sixvalley_ecommerce/utill/dimensions.dart';
import 'package:provider/provider.dart';
import 'package:shimmer/shimmer.dart';

class PredictionHubScreen extends StatefulWidget {
  final int? initialMatchId;
  const PredictionHubScreen({super.key, this.initialMatchId});

  @override
  State<PredictionHubScreen> createState() => _PredictionHubScreenState();
}

class _PredictionHubScreenState extends State<PredictionHubScreen> with SingleTickerProviderStateMixin {
  late TabController _tabController;
  final Map<int, TextEditingController> _score1Controllers = {};
  final Map<int, TextEditingController> _score2Controllers = {};

  @override
  void initState() {
    super.initState();
    // هام: تم تحديد الطول بـ 2 فقط
    _tabController = TabController(length: 2, vsync: this);
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final controller = Provider.of<PredictionController>(context, listen: false);
      controller.getMatchList();
      controller.getMyPredictions();
    });
  }

  @override
  void dispose() {
    _tabController.dispose();
    for (var c in _score1Controllers.values) { c.dispose(); }
    for (var c in _score2Controllers.values) { c.dispose(); }
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    bool isDark = Provider.of<ThemeController>(context, listen: false).darkTheme;
    
    return Scaffold(
      appBar: AppBar(
        title: Text("مركز التوقعات ⚽", style: textBold.copyWith(color: Colors.white)),
        centerTitle: true,
        elevation: 0,
        backgroundColor: Theme.of(context).primaryColor,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios, color: Colors.white),
          onPressed: () => Navigator.pop(context),
        ),
      ),
      body: Column(
        children: [
          Container(
            color: Theme.of(context).primaryColor,
            child: TabBar(
              controller: _tabController,
              indicatorColor: Colors.white,
              indicatorWeight: 3,
              labelColor: Colors.white,
              unselectedLabelColor: Colors.white.withOpacity(0.7),
              tabs: const [
                Tab(text: "المباريات النشطة"),
                Tab(text: "توقعاتي السابقة"),
              ],
            ),
          ),
          Expanded(
            child: Container(
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  begin: Alignment.topCenter, end: Alignment.bottomCenter,
                  colors: isDark 
                    ? [Theme.of(context).scaffoldBackgroundColor, Colors.black]
                    : [Colors.white, Theme.of(context).primaryColor.withOpacity(0.05)],
                ),
              ),
              child: TabBarView(
                controller: _tabController,
                children: [
                  _buildActiveMatchesTab(),
                  _buildMyPredictionsTab(),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildActiveMatchesTab() {
    return Consumer<PredictionController>(
      builder: (context, controller, child) {
        if (controller.isLoading) return _buildLoadingShimmer();
        if (controller.activeMatches.isEmpty) return _buildEmptyState(Icons.event_busy, "لا توجد مباريات نشطة حالياً للتوقع");

        return ListView.builder(
          padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
          itemCount: controller.activeMatches.length,
          itemBuilder: (context, index) {
            final match = controller.activeMatches[index];
            
            if (match.id != null && !_score1Controllers.containsKey(match.id)) {
              _score1Controllers[match.id!] = TextEditingController();
              _score2Controllers[match.id!] = TextEditingController();
            }

            return PredictionMatchCard(
              match: match,
              s1Controller: _score1Controllers[match.id]!,
              s2Controller: _score2Controllers[match.id]!,
              isSubmitLoading: controller.isSubmitLoading,
              onTapSubmit: () {
                final s1 = _score1Controllers[match.id]!.text;
                final s2 = _score2Controllers[match.id]!.text;
                if (s1.isNotEmpty && s2.isNotEmpty) {
                  controller.submitPrediction(match.id!, int.parse(s1), int.parse(s2));
                } else {
                  ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text("يرجى إدخال توقع النتيجة أولاً")));
                }
              },
            );
          },
        );
      },
    );
  }

  Widget _buildMyPredictionsTab() {
    return Consumer<PredictionController>(
      builder: (context, controller, child) {
        if (controller.isLoading) return _buildLoadingShimmer();
        if (controller.myPredictions.isEmpty) return _buildEmptyState(Icons.history, "لم تقم بإرسال أي توقعات بعد");

        return ListView.builder(
          padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
          itemCount: controller.myPredictions.length,
          itemBuilder: (context, index) {
            final p = controller.myPredictions[index];
            return Container(
              margin: const EdgeInsets.only(bottom: 12),
              padding: const EdgeInsets.all(15),
              decoration: BoxDecoration(
                color: Theme.of(context).cardColor,
                borderRadius: BorderRadius.circular(12),
                boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.02), blurRadius: 5)],
              ),
              child: Row(
                children: [
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(p.matchDetails?.title ?? "مباراة", style: textBold),
                        const SizedBox(height: 5),
                        Text("${p.matchDetails?.team1Name} vs ${p.matchDetails?.team2Name}", 
                          style: textRegular.copyWith(color: Theme.of(context).hintColor, fontSize: 12)),
                      ],
                    ),
                  ),
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.end,
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                        decoration: BoxDecoration(
                          color: Theme.of(context).primaryColor.withOpacity(0.1),
                          borderRadius: BorderRadius.circular(20),
                        ),
                        child: Text("${p.predictedTeam1Score} - ${p.predictedTeam2Score}", style: textBold),
                      ),
                      const SizedBox(height: 5),
                      Text(p.status == 'evaluated' ? "مكتمل" : "قيد الانتظار", 
                        style: textRegular.copyWith(fontSize: 10, color: p.status == 'evaluated' ? Colors.green : Colors.orange)),
                    ],
                  ),
                ],
              ),
            );
          },
        );
      },
    );
  }

  Widget _buildEmptyState(IconData icon, String message) {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(icon, size: 60, color: Theme.of(context).hintColor.withOpacity(0.3)),
          const SizedBox(height: 16),
          Text(message, style: textMedium.copyWith(color: Theme.of(context).hintColor)),
        ],
      ),
    );
  }

  Widget _buildLoadingShimmer() {
    return Shimmer.fromColors(
      baseColor: Colors.grey[300]!,
      highlightColor: Colors.grey[100]!,
      child: ListView.builder(
        itemCount: 3,
        padding: const EdgeInsets.all(16),
        itemBuilder: (_, __) => Container(
          height: 150, 
          margin: const EdgeInsets.only(bottom: 16), 
          decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(15)),
        ),
      ),
    );
  }
}
