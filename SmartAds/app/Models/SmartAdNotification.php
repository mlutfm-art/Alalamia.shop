<?php

namespace Modules\SmartAds\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * جدول smart_ad_notifications
 * ─────────────────────────────
 * يُخزّن كل إعلان/action كسجل إشعار قابل للاسترجاع
 * من مركز الإشعارات داخل التطبيق (Notification Center).
 *
 * @property int         $id
 * @property int         $smart_ad_id
 * @property int|null    $user_id            null = للجميع
 * @property string      $title
 * @property string|null $body
 * @property string|null $image_url
 * @property string      $action_type
 * @property array       $flutter_payload    الـ JSON الكامل المُرسل لـ Flutter
 * @property string      $display_type       push | inapp_banner | notification_center
 * @property bool        $is_read
 * @property \Carbon\Carbon|null $read_at
 * @property \Carbon\Carbon|null $expires_at
 */
class SmartAdNotification extends Model
{
    use HasFactory;

    protected $table = 'smart_ad_notifications';

    protected $fillable = [
        'smart_ad_id',
        'user_id',
        'title',
        'body',
        'image_url',
        'action_type',
        'flutter_payload',
        'display_type',
        'is_read',
        'read_at',
        'expires_at',
    ];

    protected $casts = [
        'flutter_payload' => 'array',
        'is_read'         => 'boolean',
        'read_at'         => 'datetime',
        'expires_at'      => 'datetime',
    ];

    /*──────────────────────────────────────────────────────────────
     | Relations
     ──────────────────────────────────────────────────────────────*/

    public function ad()
    {
        return $this->belongsTo(SmartAd::class, 'smart_ad_id');
    }

    /*──────────────────────────────────────────────────────────────
     | Scopes
     ──────────────────────────────────────────────────────────────*/

    /** الإشعارات غير المنتهية الصلاحية */
    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
        });
    }

    /** الإشعارات غير المقروءة */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /** إشعارات مستخدم معين أو إشعارات عامة */
    public function scopeForUser($query, ?int $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->whereNull('user_id');
            if ($userId) {
                $q->orWhere('user_id', $userId);
            }
        });
    }

    /** حسب نوع العرض */
    public function scopeForDisplay($query, string $displayType)
    {
        return $query->where('display_type', $displayType);
    }

    /*──────────────────────────────────────────────────────────────
     | Methods
     ──────────────────────────────────────────────────────────────*/

    /** تحديد الإشعار كمقروء */
    public function markAsRead(): bool
    {
        return $this->update(['is_read' => true, 'read_at' => now()]);
    }
}
