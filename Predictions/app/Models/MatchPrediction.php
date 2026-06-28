<?php
namespace Modules\Predictions\app\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
class MatchPrediction extends Model {
    protected $table = 'match_predictions';
    protected $fillable = ['match_id','user_id','predicted_team1','predicted_team2','points_awarded','distance_score','prediction_status'];
    protected $casts = ['match_id'=>'integer','user_id'=>'integer','predicted_team1'=>'integer','predicted_team2'=>'integer','points_awarded'=>'integer','distance_score'=>'integer'];
    public function match_details(): BelongsTo { return $this->belongsTo(PredictionMatch::class,'match_id'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class,'user_id'); }
}
