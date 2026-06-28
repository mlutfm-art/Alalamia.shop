<?php

namespace Modules\SmartAds\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SmartAd extends Model
{
    use HasFactory;

    protected $table = 'smart_ads';

    protected $fillable = [
        'title', 'image', 'video', 'ad_type', 'placement',
        'parent_id', 'ab_variant', 'action_data', 'status',
        'clicks', 'impressions', 'target_category_id',
        'target_region', 'device_type', 'start_at', 'end_at',
    ];

    protected $casts = [
        'action_data' => 'array',
        'status'      => 'boolean',
        'clicks'      => 'integer',
        'impressions' => 'integer',
        'start_at'    => 'datetime',
        'end_at'      => 'datetime',
    ];

    protected $appends = ['image_url', 'video_url', 'ctr'];

    /* ===================== Relations ===================== */

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function variants()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /* ===================== Scopes ===================== */

    public function scopeActive($query)
    {
        return $query->where('status', 1)
            ->where(fn($q) => $q->whereNull('start_at')->orWhere('start_at', '<=', now()))
            ->where(fn($q) => $q->whereNull('end_at')->orWhere('end_at', '>=', now()));
    }

    public function scopeForPlacement($query, $placement)
    {
        return $query->where('placement', $placement);
    }

    public function scopeForDevice($query, ?string $device)
    {
        if (!$device) return $query;
        return $query->where(fn($q) => $q->where('device_type', 'all')->orWhere('device_type', $device));
    }

    public function scopeForCategory($query, $categoryId)
    {
        if (!$categoryId) return $query;
        return $query->where(fn($q) => $q->whereNull('target_category_id')->orWhere('target_category_id', $categoryId));
    }

    public function scopeForRegion($query, ?string $region)
    {
        if (!$region) return $query;
        return $query->where(fn($q) => $q->whereNull('target_region')->orWhere('target_region', $region));
    }

    /* ===================== Accessors ===================== */

    /**
     * صنع URL الصورة بما يلائم بنية Hostinger حيث:
     *   - domain root = public_html/
     *   - symlink = public_html/public/storage → public_html/storage/app/public
     *   - URL الصحيح = APP_URL/public/storage/smartads/{filename}
     *
     * يدعم أيضاً:
     *   - URL كامل (http/https) → يُعاد كما هو
     *   - مسار يبدأ بـ upload/ → يضيف public/storage/ تلقائياً
     */
    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) return null;
        return self::buildStorageUrl($this->image, 'smartads');
    }

    public function getVideoUrlAttribute(): ?string
    {
        if (!$this->video) return null;
        // رابط خارجي (يوتيوب، vimeo، إلخ)
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

    /**
     * بناء URL ملف مخزّن — يحل جميع حالات المسار.
     *
     * @param string $path     القيمة المخزّنة في DB
     * @param string $folder   المجلد الافتراضي (مثل 'smartads')
     */
    public static function buildStorageUrl(string $path, string $folder = 'smartads'): string
    {
        $base = rtrim(config('app.url', 'https://alalamia.shop'), '/');

        // 1. URL كامل — أعِده كما هو
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $path = ltrim($path, '/');

        // 2. يبدأ بـ public/storage/ — أضف APP_URL فقط
        if (str_starts_with($path, 'public/storage/')) {
            return $base . '/' . $path;
        }

        // 3. يبدأ بـ storage/ — استبدله بـ public/storage/
        if (str_starts_with($path, 'storage/')) {
            return $base . '/public/' . $path;
        }

        // 4. يبدأ بـ upload/ (أسلوب 6Valley القديم)
        if (str_starts_with($path, 'upload/')) {
            return $base . '/public/storage/' . $path;
        }

        // 5. اسم ملف فقط — ضع المجلد الافتراضي
        return $base . '/public/storage/' . $folder . '/' . $path;
    }

    /* ===================== A/B helpers ===================== */

    public function pickVariant(): self
    {
        $children = $this->variants()->active()->get();
        if ($children->isEmpty()) return $this;
        return $children->random();
    }

    public function winner(): ?self
    {
        $all = $this->variants()->get()->push($this)
            ->filter(fn($a) => $a->impressions > 0);
        if ($all->isEmpty()) return null;
        return $all->sortByDesc('ctr')->first();
    }
}