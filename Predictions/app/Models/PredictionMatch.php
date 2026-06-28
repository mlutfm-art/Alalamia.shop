<?php
namespace Modules\Predictions\app\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class PredictionMatch extends Model {
    protected $table = 'prediction_matches';
    protected $fillable = ['title','team1_name','team1_logo','team2_name','team2_logo','match_time','prediction_close_time','status','actual_team1','actual_team2','reward_points','notes'];
    protected $casts = ['match_time'=>'datetime','prediction_close_time'=>'datetime','actual_team1'=>'integer','actual_team2'=>'integer','reward_points'=>'integer'];
    public function predictions(): HasMany { return $this->hasMany(MatchPrediction::class,'match_id'); }
    public function isOpen(): bool { return $this->status==='active' && now()->lt($this->prediction_close_time); }
    public function scopeActive($q) { return $q->where('status','active'); }
}
