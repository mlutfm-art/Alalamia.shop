<?php

namespace Modules\SmartAds\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DeviceToken extends Model
{
    protected $table = 'device_tokens';

    protected $fillable = [
        'tokenable_id',
        'tokenable_type',
        'token',
        'device_type',
        'browser',
        'last_used_at',
    ];

    protected $casts = [
        'last_used_at' => 'datetime',
    ];

    public function tokenable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * حفظ أو تحديث التوكن — يمنع التكرار
     */
    public static function saveToken(
        string $token,
        ?int $userId = null,
        ?string $userType = null,
        string $browser = 'unknown'
    ): self {
        return static::updateOrCreate(
            ['token' => $token],
            [
                'tokenable_id'   => $userId,
                'tokenable_type' => $userType,
                'device_type'    => 'web',
                'browser'        => $browser,
                'last_used_at'   => now(),
            ]
        );
    }
}
