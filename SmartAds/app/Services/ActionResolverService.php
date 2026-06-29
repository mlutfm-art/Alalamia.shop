<?php

namespace Modules\SmartAds\app\Services;

class ActionResolverService
{
    /**
     * العقل المدبر لتحويل أي إدخال من الإدارة إلى Payload احترافي لـ Flutter
     * تم تحصين الكود بالكامل لمنع خطأ Undefined array key "id"
     */
    public function resolve(?array $actionData): array
    {
        // إذا لم تكن هناك بيانات أو لم يتم تحديد نوع الإجراء
        if (empty($actionData) || !isset($actionData['type'])) {
            return $this->buildResponse('none', [], null, 'إجراء غير محدد');
        }

        $type = $actionData['type'];
        $payload = $actionData;

        // دالة مساعدة للحصول على القيمة بشكل آمن
        $safeId = $payload['id'] ?? ($payload['item_id'] ?? null);

        return match ($type) {
            // ── 1. التنقل والذكاء (Navigation & Intelligence) ─────
            'product'           => $this->buildResponse('product', ['id' => $safeId], $safeId ? "app://product/{$safeId}" : null),
            'category'          => $this->buildResponse('category', ['id' => $safeId], $safeId ? "app://category/{$safeId}" : null),
            'wallet'            => $this->buildResponse('wallet', [], 'app://wallet'),
            'order_tracking'    => $this->buildResponse('order_tracking', ['order_id' => $safeId]),

            // ── 2. التلعيب (Gamification) ──────
            'scratch_card'      => $this->buildResponse('scratch_card', ['reward' => $payload['coupon'] ?? 'GIFT10'], null, 'امسح واربح مكافأتك! 🎁'),
            'spin_wheel'        => $this->buildResponse('spin_wheel', ['campaign_id' => $safeId ?? 1], null, 'جرب حظك مع عجلة الحظ! 🎡'),

            // ── 3. محرك الاستعجال (Urgency) ───────────────
            'countdown'         => $this->buildResponse('countdown', ['end_time' => $payload['countdown'] ?? 3600], null, 'العرض ينتهي قريباً! ⏳'),

            // ── 4. التواصل (Social) ───────────────
            'whatsapp_chat'     => $this->buildResponse('whatsapp', ['phone' => $payload['phone'] ?? ''], "https://wa.me/" . ($payload['phone'] ?? '')),
            'facebook_follow'   => $this->buildResponse('facebook', ['id' => $payload['id'] ?? ''], "fb://profile/" . ($payload['id'] ?? '')),
            'instagram_follow'  => $this->buildResponse('instagram', ['username' => $payload['username'] ?? ''], "instagram://user?username=" . ($payload['username'] ?? '')),

            // ── 5. الخدمات (Utils) ────────────────────
            'apply_coupon'      => $this->buildResponse('coupon', ['code' => $payload['coupon'] ?? ''], null, 'تم تفعيل الكوبون تلقائياً! ✅'),
            'copy_clipboard'    => $this->buildResponse('copy', ['text' => $payload['text'] ?? ''], null, 'تم النسخ إلى الحافظة'),

            // ── Fallback ─────────────────────────────────────────
            default             => $this->buildResponse('external_url', ['url' => $payload['url'] ?? ''], $payload['url'] ?? ''),
        };
    }

    private function buildResponse(string $type, array $payload, ?string $deepLink = null, string $msg = 'جاري المعالجة...'): array
    {
        return [
            'action_type'  => $type,
            'payload'      => $payload,
            'deep_link'    => $deepLink,
            'feedback'     => [
                'message'       => $msg,
                'show_snackbar' => !empty($msg)
            ]
        ];
    }
}
