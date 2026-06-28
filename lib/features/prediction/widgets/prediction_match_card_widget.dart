import 'package:flutter/material.dart';
import 'package:flutter_sixvalley_ecommerce/features/prediction/domain/models/prediction_model.dart';
import 'package:flutter_sixvalley_ecommerce/utill/custom_themes.dart';
import 'package:flutter_sixvalley_ecommerce/utill/dimensions.dart';
import 'package:intl/intl.dart';

class PredictionMatchCard extends StatelessWidget {
  final MatchModel match;
  final TextEditingController s1Controller;
  final TextEditingController s2Controller;
  final bool isSubmitLoading;
  final Function() onTapSubmit;

  const PredictionMatchCard({
    super.key,
    required this.match,
    required this.s1Controller,
    required this.s2Controller,
    required this.isSubmitLoading,
    required this.onTapSubmit,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 20),
      decoration: BoxDecoration(
        color: Theme.of(context).cardColor,
        borderRadius: BorderRadius.circular(Dimensions.paddingSizeDefault),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.05),
            blurRadius: 10,
            offset: const Offset(0, 5),
          )
        ],
        border: Border.all(color: Theme.of(context).primaryColor.withOpacity(0.1)),
      ),
      child: Column(
        children: [
          // Header: Title & Reward
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 15, vertical: 8),
            decoration: BoxDecoration(
              color: Theme.of(context).primaryColor.withOpacity(0.05),
              borderRadius: const BorderRadius.vertical(top: Radius.circular(Dimensions.paddingSizeDefault)),
            ),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(match.title ?? "مباراة قادمة", style: textMedium.copyWith(fontSize: Dimensions.fontSizeSmall)),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                  decoration: BoxDecoration(
                    color: Colors.orange.withOpacity(0.1),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: Text("${match.rewardPoints} نقطة 🎁", 
                    style: textBold.copyWith(color: Colors.orange, fontSize: Dimensions.fontSizeExtraSmall)),
                ),
              ],
            ),
          ),

          Padding(
            padding: const EdgeInsets.all(20),
            child: Column(
              children: [
                // Teams Row
                Row(
                  children: [
                    Expanded(child: _buildTeamInfo(context, match.team1Name ?? "Team A", match.team1Logo)),
                    Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 10),
                      child: Column(
                        children: [
                          Text("VS", style: textBold.copyWith(fontSize: 22, color: Theme.of(context).primaryColor)),
                          if(match.predictionCloseTime != null)
                             Text(DateFormat('hh:mm a').format(DateTime.parse(match.predictionCloseTime!)),
                               style: textRegular.copyWith(fontSize: 10, color: Theme.of(context).hintColor)),
                        ],
                      ),
                    ),
                    Expanded(child: _buildTeamInfo(context, match.team2Name ?? "Team B", match.team2Logo)),
                  ],
                ),

                const SizedBox(height: 25),
                const Divider(),
                const SizedBox(height: 15),

                // Prediction Inputs
                Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    _buildScoreInput(context, s1Controller),
                    const Padding(
                      padding: EdgeInsets.symmetric(horizontal: 20),
                      child: Text(":", style: TextStyle(fontSize: 30, fontWeight: FontWeight.bold)),
                    ),
                    _buildScoreInput(context, s2Controller),
                  ],
                ),

                const SizedBox(height: 20),
                
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton(
                    onPressed: isSubmitLoading ? null : onTapSubmit,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Theme.of(context).primaryColor,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      padding: const EdgeInsets.symmetric(vertical: 12),
                    ),
                    child: isSubmitLoading 
                      ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                      : Text("توقع واربح الآن ⚡", style: textBold.copyWith(color: Colors.white)),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildTeamInfo(BuildContext context, String name, String? logo) {
    return Column(
      children: [
        Container(
          width: 65, height: 65,
          decoration: BoxDecoration(
            color: Theme.of(context).primaryColor.withOpacity(0.05),
            shape: BoxShape.circle,
            border: Border.all(color: Theme.of(context).primaryColor.withOpacity(0.1)),
          ),
          child: ClipOval(
            child: logo != null && logo.isNotEmpty
                ? Image.network(logo, fit: BoxFit.cover, errorBuilder: (c, e, s) => const Icon(Icons.sports_soccer, size: 30))
                : const Icon(Icons.sports_soccer, size: 30),
          ),
        ),
        const SizedBox(height: 10),
        Text(name, style: textBold, textAlign: TextAlign.center, maxLines: 2, overflow: TextOverflow.ellipsis),
      ],
    );
  }

  Widget _buildScoreInput(BuildContext context, TextEditingController controller) {
    return SizedBox(
      width: 60,
      child: TextField(
        controller: controller,
        textAlign: TextAlign.center,
        keyboardType: TextInputType.number,
        maxLength: 2,
        style: textBold.copyWith(fontSize: 24),
        decoration: InputDecoration(
          counterText: "",
          filled: true,
          fillColor: Theme.of(context).primaryColor.withOpacity(0.05),
          contentPadding: const EdgeInsets.symmetric(vertical: 10),
          border: OutlineInputBorder(
            borderRadius: BorderRadius.circular(12),
            borderSide: BorderSide(color: Theme.of(context).primaryColor.withOpacity(0.2)),
          ),
          hintText: "0",
        ),
      ),
    );
  }
}
