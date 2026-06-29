<?php

namespace Modules\SmartAds\app\Services;

use App\Models\User;
use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;

class TargetingService
{
    /**
     * بناء استعلام المستخدمين المستهدفين بناءً على الفئة المختارة
     */
    public function buildTargetQuery(string $targetType, array $config, bool $onlyFcm = true): Builder
    {
        $query = User::query();

        if ($onlyFcm) {
            $query->whereNotNull('cm_firebase_token')->where('cm_firebase_token', '!=', '');
        }

        switch ($targetType) {
            case 'product_buyers':
                if (!empty($config['product_id'])) {
                    $query->whereHas('orders', function($q) use ($config) {
                        $q->whereHas('details', function($sq) use ($config) {
                            $sq->where('product_id', $config['product_id']);
                        });
                    });
                }
                break;

            case 'category_buyers':
                if (!empty($config['category_id'])) {
                    $query->whereHas('orders', function($q) use ($config) {
                        $q->whereHas('details', function($sq) use ($config) {
                            $sq->whereHas('product', function($pq) use ($config) {
                                $pq->where('category_id', $config['category_id']);
                            });
                        });
                    });
                }
                break;

            case 'city':
                if (!empty($config['city'])) {
                    $query->where('city', 'like', "%{$config['city']}%");
                }
                break;

            case 'order_status':
                if (!empty($config['order_status'])) {
                    $query->whereHas('orders', function($q) use ($config) {
                        $q->where('order_status', $config['order_status']);
                    });
                }
                break;

            case 'price_range':
                $min = $config['min_price'] ?? 0;
                $max = $config['max_price'] ?? 999999;
                $query->whereHas('orders', function($q) use ($min, $max) {
                    $q->select('customer_id')
                      ->groupBy('customer_id')
                      ->havingRaw('SUM(order_amount) BETWEEN ? AND ?', [$min, $max]);
                });
                break;

            case 'last_order_days':
                $days = $config['days'] ?? 30;
                $query->whereHas('orders', function($q) use ($days) {
                    $q->where('created_at', '>=', now()->subDays($days));
                });
                break;

            case 'registered_days':
                $days = $config['days'] ?? 30;
                $query->where('created_at', '>=', now()->subDays($days));
                break;
        }

        return $query;
    }
}
