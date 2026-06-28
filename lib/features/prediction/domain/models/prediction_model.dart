class PredictionBannerModel {
  bool? showBanner;
  String? title;
  String? description;
  String? image;
  String? buttonText;
  int? matchId;
  String? action;
  String? team1;
  String? team2;
  String? matchTitle;

  PredictionBannerModel({
    this.showBanner,
    this.title,
    this.description,
    this.image,
    this.buttonText,
    this.matchId,
    this.action,
    this.team1,
    this.team2,
    this.matchTitle,
  });

  PredictionBannerModel.fromJson(Map<String, dynamic> json) {
    showBanner = json['show_banner'] == true || json['show_banner'] == 1 || json['show_banner'] == "1" || json['status'] == 1 || json['status'] == "1";
    title = json['title'];
    description = json['description'];
    image = json['image'];
    buttonText = json['button_text'];
    matchId = int.tryParse(json['match_id'].toString());
    action = json['action'];
    team1 = json['team1'];
    team2 = json['team2'];
    matchTitle = json['match_title'];
  }
}

class MatchModel {
  int? id;
  String? team1Name;
  String? team1Logo;
  String? team2Name;
  String? team2Logo;
  String? predictionCloseTime;
  int? rewardPoints;
  String? title;
  bool? isExpired;

  MatchModel({
    this.id,
    this.team1Name,
    this.team1Logo,
    this.team2Name,
    this.team2Logo,
    this.predictionCloseTime,
    this.rewardPoints,
    this.title,
    this.isExpired,
  });

  MatchModel.fromJson(Map<String, dynamic> json) {
    id = int.tryParse(json['id'].toString());
    team1Name = json['team1_name'];
    team1Logo = json['team1_logo'];
    team2Name = json['team2_name'];
    team2Logo = json['team2_logo'];
    predictionCloseTime = json['prediction_close_time'];
    rewardPoints = int.tryParse(json['reward_points'].toString());
    title = json['title'];
    isExpired = json['is_expired'] == true || json['is_expired'] == 1 || json['is_expired'] == "1";
  }
}

class PredictionModel {
  int? id;
  int? matchId;
  int? predictedTeam1Score;
  int? predictedTeam2Score;
  String? status;
  int? pointsAwarded;
  MatchModel? matchDetails;

  PredictionModel({
    this.id,
    this.matchId,
    this.predictedTeam1Score,
    this.predictedTeam2Score,
    this.status,
    this.pointsAwarded,
    this.matchDetails,
  });

  PredictionModel.fromJson(Map<String, dynamic> json) {
    id = int.tryParse(json['id'].toString());
    matchId = int.tryParse(json['match_id'].toString());
    predictedTeam1Score = int.tryParse(json['predicted_team1'].toString());
    predictedTeam2Score = int.tryParse(json['predicted_team2'].toString());
    status = json['prediction_status'];
    pointsAwarded = int.tryParse(json['points_awarded']?.toString() ?? '0');
    if (json['match_details'] != null) {
      matchDetails = MatchModel.fromJson(json['match_details']);
    }
  }
}

class LeaderboardModel {
  int? userId;
  String? userName;
  String? userImage;
  int? totalPoints;
  int? rank;

  LeaderboardModel({this.userId, this.userName, this.userImage, this.totalPoints, this.rank});

  LeaderboardModel.fromJson(Map<String, dynamic> json) {
    userId = int.tryParse(json['user_id'].toString());
    userName = json['user_name'];
    userImage = json['user_image'];
    totalPoints = int.tryParse(json['total_points'].toString());
    rank = int.tryParse(json['rank'].toString());
  }
}
