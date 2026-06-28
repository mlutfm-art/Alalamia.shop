<?php

namespace Modules\SmartAds\app\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class TargetingService
{
    /**
     * بناء استعلام المستخدمين حسب نوع الاستهداف
     */
    public function buildTargetQuery(string $targetType, array $targetValue, bool $onlyFcm = false)
    {
        $query = User::query();

        if ($onlyFcm) {
            $query->whereNotNull('cm_firebase_token')
                  ->where('cm_firebase_token', '!=', '');
        }

        switch ($targetType) {
            case 'all':
                break;
            case 'customer':
                $customerId = $targetValue['customer_id'] ?? null;
                if ($customerId) {
                    $query->where('id', $customerId);
                }
                break;
            case 'product':
                $productId = $targetValue['product_id'] ?? null;
                if ($productId) {
                    $query->whereHas('orders.items', function($q) use ($productId) {
                        $q->where('product_id', $productId);
                    });
                }
                break;
            case 'category':
                $categoryId = $targetValue['category_id'] ?? null;
                if ($categoryId) {
                    $query->whereHas('orders.items.product', function($q) use ($categoryId) {
                        $q->where('category_id', $categoryId);
                    });
                }
                break;
        }

        return $query;
    }
}
