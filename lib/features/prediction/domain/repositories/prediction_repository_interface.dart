import 'package:flutter_sixvalley_ecommerce/interface/repository_interface.dart';

abstract class PredictionRepositoryInterface implements RepositoryInterface {
  Future<dynamic> getActiveMatch();
  Future<dynamic> submitPrediction(int matchId, int team1Score, int team2Score);
  Future<dynamic> getLeaderboard();
  Future<dynamic> getMyPredictions();
  Future<dynamic> getActiveBanner();
}
