<?php

namespace Modules\SmartAds\app\Services;

class ActionResolverService
{
    /**
     * العقل المدبر لتحويل أي إدخال من الإدارة إلى Payload احترافي لـ Flutter
     * يدعم كافة الأنواع: التنقل، التلعيب، التواصل الاجتماعي، والخدمات.
     */
    public function resolve(?array $actionData): array
    {
        if (empty($actionData) || !isset($actionData['type'])) {
            return $this->buildResponse('none', [], null, 'إجراء غير محدد');
        }

        $type = $actionData['type'];
        $payload = $actionData;

        // استخراج القيم بشكل آمن
        $safeId = $payload['id'] ?? ($payload['item_id'] ?? null);
        $safeUrl = $payload['url'] ?? ($payload['external_url'] ?? '');
        $safePhone = $payload['phone'] ?? ($payload['wa_phone'] ?? '');

        return match ($type) {
            // ── 1. التنقل والذكاء (Navigation) ─────
            'product'           => $this->buildResponse('product', ['id' => $safeId], "app://product/{$safeId}"),
            'category'          => $this->buildResponse('category', ['id' => $safeId], "app://category/{$safeId}"),
            'brand'             => $this->buildResponse('brand', ['id' => $safeId], "app://brand/{$safeId}"),
            'flash_deals'       => $this->buildResponse('flash_deals', [], 'app://flash_deals'),
            'wallet'            => $this->buildResponse('wallet', [], 'app://wallet'),
            'order_tracking'    => $this->buildResponse('order_tracking', ['order_id' => $safeId], "app://track_order/{$safeId}"),

            // ── 2. التلعيب (Gamification) ──────
            'scratch_card'      => $this->buildResponse('scratch_card', ['reward' => $payload['coupon'] ?? 'GIFT10'], null, 'امسح واربح مكافأتك! 🎁'),
            'spin_wheel'        => $this->buildResponse('spin_wheel', ['campaign_id' => $safeId ?? 1], null, 'جرب حظك مع عجلة الحظ! 🎡'),

            // ── 3. محرك الاستعجال (Urgency) ───────────────
            'countdown'         => $this->buildResponse('countdown', ['end_time' => $payload['countdown'] ?? 3600], null, 'العرض ينتهي قريباً! ⏳'),

            // ── 4. التواصل المباشر (Direct Comm) ───────────────
            'whatsapp_chat'     => $this->buildResponse('whatsapp', ['phone' => $safePhone], "https://wa.me/" . preg_replace('/[^0-9]/', '', $safePhone)),
            'call_phone'        => $this->buildResponse('call', ['phone' => $safePhone], "tel:{$safePhone}"),
            'save_contact'      => $this->buildResponse('save_contact', ['phone' => $safePhone, 'name' => 'الدعم الفني']),

            // ── 5. التواصل الاجتماعي (Social) ───────────────
            'facebook_follow'   => $this->buildResponse('facebook', ['id' => $safeId], "fb://profile/{$safeId}"),
            'instagram_follow'  => $this->buildResponse('instagram', ['username' => $safeUrl], "instagram://user?username={$safeUrl}"),
            'tiktok_follow'     => $this->buildResponse('tiktok', ['username' => $safeUrl], "https://www.tiktok.com/@{$safeUrl}"),
            'youtube_subscribe' => $this->buildResponse('youtube', ['channel_id' => $safeUrl], "https://www.youtube.com/channel/{$safeUrl}"),
            'telegram_join'     => $this->buildResponse('telegram', ['username' => $safeUrl], "https://t.me/{$safeUrl}"),

            // ── 6. الخدمات (Utils) ────────────────────
            'apply_coupon'      => $this->buildResponse('coupon', ['code' => $payload['coupon'] ?? ''], null, 'تم تفعيل الكوبون تلقائياً! ✅'),
            'copy_to_clipboard' => $this->buildResponse('copy', ['text' => $safeUrl ?? $payload['text'] ?? ''], null, 'تم النسخ إلى الحافظة'),
            'survey'            => $this->buildResponse('survey', ['url' => $safeUrl], $safeUrl, 'نقدر رأيك! 📝'),
            'external_url'      => $this->buildResponse('external_url', ['url' => $safeUrl], $safeUrl),

            default             => $this->buildResponse('external_url', ['url' => $safeUrl], $safeUrl),
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
