<?php
namespace Modules\SmartAds\app\Models;
use Illuminate\Database\Eloquent\Model;

class SmartAdSchedule extends Model
{
    protected $fillable = ['ad_id','title','body','image','target_type','target_value','scheduled_at','status'];
    protected $casts = ['target_value'=>'array','scheduled_at'=>'datetime'];
    public function ad() { return $this->belongsTo(SmartAd::class, 'ad_id'); }
}
