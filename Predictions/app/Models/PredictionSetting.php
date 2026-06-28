<?php
namespace Modules\Predictions\app\Models;
use Illuminate\Database\Eloquent\Model;
class PredictionSetting extends Model {
    protected $table = 'prediction_settings';
    protected $fillable = ['key','value'];
    public static function get(string $key, mixed $default=null): mixed {
        $row = static::where('key',$key)->first();
        return $row ? $row->value : $default;
    }
    public static function set(string $key, mixed $value): void {
        static::updateOrCreate(['key'=>$key],['value'=>$value]);
    }
}
