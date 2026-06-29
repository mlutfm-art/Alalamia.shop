<?php

namespace Modules\SmartAds\app\Services;

use App\Models\User;
use App\Models\Order;
use App\Models\Cart;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class TargetingService
{
    /**
     * بناء استعلام المستخدمين المستهدفين بناءً على تكوين الاستهداف
     */
    public function buildTargetQuery(string $targetType, array $config, bool $onlyFcm = true): Builder
    {
        $query = User::query();

        if ($onlyFcm) {
            $query->whereNotNull('cm_firebase_token')->where('cm_firebase_token', '!=', '');
        }

        // ── 1. الاستهداف الجغرافي ──────────────────
        if (!empty($config['countries'])) {
            $query->whereIn('country', (array)$config['countries']);
        }
        if (!empty($config['cities'])) {
            $query->whereIn('city', (array)$config['cities']);
        }

        // ── 2. الاستهداف السلوكي (Behavioral) ──────
        switch ($targetType) {
            case 'abandoned_cart':
                // مستخدمين لديهم سلة بها منتجات ولم يطلبوا منذ X ساعات
                $hours = $config['hours'] ?? 24;
                $query->whereHas('carts', function($q) use ($hours) {
                    $q->where('updated_at', '<', now()->subHours($hours));
                })->whereDoesntHave('orders', function($q) use ($hours) {
                    $q->where('created_at', '>', now()->subHours($hours));
                });
                break;

            case 'vip_customers':
                // عملاء صرفوا أكثر من X ريال
                $minSpend = $config['min_spend'] ?? 1000;
                $query->whereHas('orders', function($q) use ($minSpend) {
                    $q->select('customer_id')
                      ->groupBy('customer_id')
                      ->havingRaw('SUM(order_amount) >= ?', [$minSpend]);
                });
                break;

            case 'inactive_users':
                // لم يفتحوا التطبيق منذ X أيام
                $days = $config['days'] ?? 30;
                $query->where('last_active_at', '<', now()->subDays($days));
                break;
                
            case 'product_interested':
                // شاهدوا منتج معين أو صنف معين
                if(!empty($config['category_id'])) {
                    $query->whereJsonContains('interests', $config['category_id']);
                }
                break;
        }

        // ── 3. تصفية الأجهزة ──────────────────────
        if (!empty($config['device_type']) && $config['device_type'] !== 'all') {
            $query->where('device_type', $config['device_type']);
        }

        return $query;
    }
}
