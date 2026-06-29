<?php

namespace Modules\SmartAds\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Product;
use App\Models\Category;

class SmartAd extends Model
{
    use HasFactory;

    protected $table = 'smart_ads';

    protected $fillable = [
        'title', 'sub_title', 'image', 'video', 'ad_type', 'action_type', 'placement',
        'parent_id', 'ab_variant', 'action_data', 'status',
        'clicks', 'impressions', 'target_category_id',
        'target_region', 'device_type', 'start_at', 'end_at',
        'button_text', 'firebase_payload', 'dynamic_context',
        'targeting_config', 'triggers_config', 'scheduling_config',
        'sent_count', 'delivered_count', 'opened_count', 'conversion_count',
        'is_ai_generated'
    ];

    protected $casts = [
        'action_data'       => 'array',
        'firebase_payload'  => 'array',
        'dynamic_context'   => 'array',
        'targeting_config'  => 'array',
        'triggers_config'   => 'array',
        'scheduling_config' => 'array',
        'status'            => 'boolean',
        'clicks'            => 'integer',
        'impressions'       => 'integer',
        'sent_count'        => 'integer',
        'delivered_count'   => 'integer',
        'opened_count'      => 'integer',
        'conversion_count'  => 'integer',
        'is_ai_generated'   => 'boolean',
        'start_at'          => 'datetime',
        'end_at'            => 'datetime',
    ];

    protected $appends = ['image_url', 'video_url', 'ctr', 'conversion_rate'];

    /* ===================== Relations ===================== */

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function variants()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /* ===================== Accessors ===================== */

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) return null;
        return self::buildStorageUrl($this->image, 'smartads');
    }

    public function getVideoUrlAttribute(): ?string
    {
        if (!$this->video) return null;
        if (filter_var($this->video, FILTER_VALIDATE_URL)) {
            return $this->video;
        }
        return self::buildStorageUrl($this->video, 'smartads/videos');
    }

    public function getCtrAttribute(): float
    {
        if (!$this->impressions || $this->impressions == 0) return 0.0;
        return round(($this->clicks / $this->impressions) * 100, 2);
    }

    public function getConversionRateAttribute(): float
    {
        if (!$this->clicks || $this->clicks == 0) return 0.0;
        return round(($this->conversion_count / $this->clicks) * 100, 2);
    }

    public static function buildStorageUrl(string $path, string $folder = 'smartads'): string
    {
        $base = rtrim(config('app.url', 'https://alalamia.shop'), '/');
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
        $path = ltrim($path, '/');
        if (str_starts_with($path, 'public/storage/')) {
            return $base . '/' . $path;
        }
        return $base . '/public/storage/' . $folder . '/' . $path;
    }
    
    /* ===================== Scopes ===================== */

    public function scopeActive($query)
    {
        return $query->where('status', 1)
            ->where(fn($q) => $q->whereNull('start_at')->orWhere('start_at', '<=', now()))
            ->where(fn($q) => $q->whereNull('end_at')->orWhere('end_at', '>=', now()));
    }
}
