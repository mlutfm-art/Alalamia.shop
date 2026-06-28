<?php

namespace Modules\SmartAds\app\Services;

/**
 * ╔══════════════════════════════════════════════════════════════════╗
 * ║            ActionResolverService — Smart Engagement Engine       ║
 * ║  العقل المدبر الذي يحوّل action_data إلى payload جاهز لـ Flutter ║
 * ╚══════════════════════════════════════════════════════════════════╝
 *
 * الاستخدام:
 *   $resolved = app(ActionResolverService::class)->resolve($ad->action_data);
 *   // $resolved = ['action' => [...], 'deep_link' => '...', 'feedback' => [...]]
 */
class ActionResolverService
{
    /*──────────────────────────────────────────────────────────────
     | 1. نقطة الدخول الرئيسية
     ──────────────────────────────────────────────────────────────*/

    /**
     * حلّ action_data وإرجاع payload موحد جاهز لـ Flutter.
     *
     * @param  array|null  $actionData   قيمة حقل action_data من جدول smart_ads
     * @return array{
     *   action_type: string,
     *   payload:     array,
     *   deep_link:   string|null,
     *   fallback_url:string|null,
     *   feedback:    array,
     *   tracking:    array,
     * }
     */
    public function resolve(?array $actionData): array
    {
        if (empty($actionData) || empty($actionData['type'])) {
            return $this->empty();
        }

        $type    = (string) $actionData['type'];
        $payload = $actionData['payload'] ?? $actionData; // دعم الهيكل القديم والجديد

        return match (true) {
            // ── 1. التنقل الداخلي ──────────────────────────────
            $type === 'product'           => $this->resolveProduct($payload),
            $type === 'category'          => $this->resolveCategory($payload),
            $type === 'brand'             => $this->resolveBrand($payload),
            $type === 'deals'             => $this->resolveDeals($payload),
            $type === 'wallet'            => $this->resolveWallet($payload),
            $type === 'order_tracking'    => $this->resolveOrderTracking($payload),
            $type === 'account_settings'  => $this->resolveAccountSettings($payload),

            // ── 2. التفاعل الاجتماعي — متابعة/اشتراك ──────────
            $type === 'facebook_follow'   => $this->resolveSocialFollow($payload, 'facebook',  'fb://profile/{id}'),
            $type === 'instagram_follow'  => $this->resolveSocialFollow($payload, 'instagram', 'instagram://user?username={id}'),
            $type === 'tiktok_follow'     => $this->resolveSocialFollow($payload, 'tiktok',    'snssdk1233://user/profile/{id}'),
            $type === 'x_follow'          => $this->resolveSocialFollow($payload, 'x',         'twitter://user?screen_name={id}'),
            $type === 'telegram_join'     => $this->resolveSocialFollow($payload, 'telegram',  'tg://resolve?domain={id}'),
            $type === 'youtube_subscribe' => $this->resolveSocialFollow($payload, 'youtube',   'vnd.youtube://www.youtube.com/channel/{id}'),

            // ── 3. التفاعل الاجتماعي — إعجاب/تفاعل ──────────
            $type === 'facebook_like_post'    => $this->resolveSocialInteract($payload, 'facebook', 'fb://facewebmodal/news/posts/{id}'),
            $type === 'instagram_like_post'   => $this->resolveSocialInteract($payload, 'instagram', 'instagram://media?id={id}'),
            $type === 'telegram_view_post'    => $this->resolveSocialInteract($payload, 'telegram',  'tg://resolve?domain={channel}&post={id}'),

            // ── 4. تواصل مباشر ─────────────────────────────────
            $type === 'whatsapp_chat'     => $this->resolveWhatsAppChat($payload),

            // ── 5. حفظ جهات الاتصال ────────────────────────────
            $type === 'save_contact'      => $this->resolveSaveContact($payload),

            // ── 6. تفاعلية وخدمية ──────────────────────────────
            $type === 'survey'            => $this->resolveSurvey($payload),
            $type === 'feedback'          => $this->resolveFeedback($payload),
            $type === 'invite_friend'     => $this->resolveInviteFriend($payload),
            $type === 'loyalty_points'    => $this->resolveLoyaltyPoints($payload),
            $type === 'api_post'          => $this->resolveApiPost($payload),
            $type === 'apply_coupon'      => $this->resolveApplyCoupon($payload),
            $type === 'copy_to_clipboard' => $this->resolveCopyToClipboard($payload),
            $type === 'notify_admin'      => $this->resolveNotifyAdmin($payload),

            // ── 7. وسائط خارجية ────────────────────────────────
            $type === 'external_url'      => $this->resolveExternalUrl($payload),
            $type === 'inapp_browser'     => $this->resolveInAppBrowser($payload),
            $type === 'app_store_rate'    => $this->resolveAppStoreRate($payload),

            // ── fallback ─────────────────────────────────────────
            default => $this->resolveExternalUrl(['url' => $payload['target'] ?? $payload['url'] ?? null]),
        };
    }

    /*──────────────────────────────────────────────────────────────
     | 2. مجموعة التنقل الداخلي
     ──────────────────────────────────────────────────────────────*/

    private function resolveProduct(array $payload): array
    {
        $id   = $payload['id'] ?? null;
        $slug = $payload['slug'] ?? null;

        return $this->build(
            type: 'product',
            payload: ['product_id' => $id, 'slug' => $slug],
            deepLink: "app://product/{$id}",
            fallback: url("/products/{$slug}"),
            feedbackKey: 'product_opened',
            feedbackMsg: 'تم فتح المنتج',
        );
    }

    private function resolveCategory(array $payload): array
    {
        $id   = $payload['id'] ?? null;
        $slug = $payload['slug'] ?? null;

        return $this->build(
            type: 'category',
            payload: ['category_id' => $id, 'slug' => $slug],
            deepLink: "app://category/{$id}",
            fallback: url("/categories/{$slug}"),
            feedbackKey: 'category_opened',
            feedbackMsg: 'تم فتح القسم',
        );
    }

    private function resolveBrand(array $payload): array
    {
        $id   = $payload['id'] ?? null;
        $slug = $payload['slug'] ?? null;

        return $this->build(
            type: 'brand',
            payload: ['brand_id' => $id, 'slug' => $slug],
            deepLink: "app://brand/{$id}",
            fallback: url("/brands/{$slug}"),
            feedbackKey: 'brand_opened',
            feedbackMsg: 'تم فتح العلامة التجارية',
        );
    }

    private function resolveDeals(array $payload): array
    {
        return $this->build(
            type: 'deals',
            payload: ['filter' => $payload['filter'] ?? 'all'],
            deepLink: 'app://deals',
            fallback: url('/deals'),
            feedbackKey: 'deals_opened',
            feedbackMsg: 'تم فتح العروض',
        );
    }

    private function resolveWallet(array $payload): array
    {
        return $this->build(
            type: 'wallet',
            payload: ['tab' => $payload['tab'] ?? 'balance'],
            deepLink: 'app://wallet',
            fallback: url('/wallet'),
            feedbackKey: 'wallet_opened',
            feedbackMsg: 'تم فتح المحفظة',
        );
    }

    private function resolveOrderTracking(array $payload): array
    {
        $orderId = $payload['order_id'] ?? null;

        return $this->build(
            type: 'order_tracking',
            payload: ['order_id' => $orderId],
            deepLink: $orderId ? "app://order/{$orderId}" : 'app://orders',
            fallback: url($orderId ? "/orders/{$orderId}" : '/orders'),
            feedbackKey: 'order_tracking_opened',
            feedbackMsg: 'تم فتح تتبع الطلب',
        );
    }

    private function resolveAccountSettings(array $payload): array
    {
        $section = $payload['section'] ?? 'main'; // main|profile|address|security

        return $this->build(
            type: 'account_settings',
            payload: ['section' => $section],
            deepLink: "app://settings/{$section}",
            fallback: url('/account/settings'),
            feedbackKey: 'settings_opened',
            feedbackMsg: 'تم فتح الإعدادات',
        );
    }

    /*──────────────────────────────────────────────────────────────
     | 3. التفاعل الاجتماعي — متابعة/اشتراك
     ──────────────────────────────────────────────────────────────*/

    /**
     * @param  string  $platform   facebook|instagram|tiktok|x|telegram|youtube
     * @param  string  $deepLinkTpl  قالب الـ deep link ({id} = handle/username/channel-id)
     */
    private function resolveSocialFollow(array $payload, string $platform, string $deepLinkTpl): array
    {
        $profileId = $payload['profile_id'] ?? $payload['username'] ?? $payload['channel_id'] ?? null;
        $profileUrl = $payload['profile_url'] ?? $payload['url'] ?? null;

        $deepLink = $profileId
            ? str_replace('{id}', $profileId, $deepLinkTpl)
            : null;

        return $this->build(
            type: "{$platform}_follow",
            payload: [
                'platform'    => $platform,
                'profile_id'  => $profileId,
                'profile_url' => $profileUrl,
            ],
            deepLink: $deepLink,
            fallback: $profileUrl,
            feedbackKey: 'social_follow_success',
            feedbackMsg: "تمت المتابعة على {$platform} بنجاح! 🎉",
        );
    }

    /*──────────────────────────────────────────────────────────────
     | 4. التفاعل الاجتماعي — إعجاب/تفاعل
     ──────────────────────────────────────────────────────────────*/

    private function resolveSocialInteract(array $payload, string $platform, string $deepLinkTpl): array
    {
        $postId     = $payload['post_id'] ?? $payload['id'] ?? null;
        $channelId  = $payload['channel_id'] ?? $payload['username'] ?? null;
        $postUrl    = $payload['post_url'] ?? $payload['url'] ?? null;

        $deepLink = $postId
            ? str_replace(['{id}', '{channel}'], [$postId, $channelId ?? ''], $deepLinkTpl)
            : null;

        return $this->build(
            type: "{$platform}_interact",
            payload: [
                'platform'   => $platform,
                'post_id'    => $postId,
                'channel_id' => $channelId,
                'post_url'   => $postUrl,
            ],
            deepLink: $deepLink,
            fallback: $postUrl,
            feedbackKey: 'social_interact_success',
            feedbackMsg: "تم التفاعل مع المنشور بنجاح! 👍",
        );
    }

    /*──────────────────────────────────────────────────────────────
     | 5. تواصل مباشر — WhatsApp
     ──────────────────────────────────────────────────────────────*/

    private function resolveWhatsAppChat(array $payload): array
    {
        $phone   = preg_replace('/[^0-9]/', '', $payload['phone'] ?? '');
        $message = urlencode($payload['message'] ?? '');

        $waDeepLink = "whatsapp://send?phone={$phone}&text={$message}";
        $waFallback = "https://wa.me/{$phone}?text={$message}";

        return $this->build(
            type: 'whatsapp_chat',
            payload: ['phone' => $phone, 'message' => rawurldecode($message)],
            deepLink: $waDeepLink,
            fallback: $waFallback,
            feedbackKey: 'whatsapp_opened',
            feedbackMsg: 'تم فتح المحادثة على WhatsApp',
        );
    }

    /*──────────────────────────────────────────────────────────────
     | 6. حفظ جهة الاتصال
     ──────────────────────────────────────────────────────────────*/

    private function resolveSaveContact(array $payload): array
    {
        return $this->build(
            type: 'save_contact',
            payload: [
                'name'       => $payload['name'] ?? '',
                'phone'      => $payload['phone'] ?? '',
                'company'    => $payload['company'] ?? '',
                'email'      => $payload['email'] ?? '',
                'website'    => $payload['website'] ?? '',
                'vcard_url'  => $payload['vcard_url'] ?? null, // اختياري: رابط vCard جاهز
            ],
            deepLink: null, // Flutter يتعامل مع هذا مباشرة عبر contacts plugin
            fallback: null,
            feedbackKey: 'contact_saved',
            feedbackMsg: 'تم حفظ جهة الاتصال في هاتفك! 📱',
        );
    }

    /*──────────────────────────────────────────────────────────────
     | 7. تفاعلية وخدمية
     ──────────────────────────────────────────────────────────────*/

    private function resolveSurvey(array $payload): array
    {
        $surveyUrl = $payload['survey_url'] ?? $payload['url'] ?? null;

        return $this->build(
            type: 'survey',
            payload: [
                'survey_id'  => $payload['survey_id'] ?? null,
                'survey_url' => $surveyUrl,
                'title'      => $payload['title'] ?? 'استبيان',
            ],
            deepLink: $surveyUrl ? "app://survey?url=" . urlencode($surveyUrl) : null,
            fallback: $surveyUrl,
            feedbackKey: 'survey_completed',
            feedbackMsg: 'شكراً على مشاركتك في الاستبيان! 🙏',
        );
    }

    private function resolveFeedback(array $payload): array
    {
        return $this->build(
            type: 'feedback',
            payload: [
                'order_id'   => $payload['order_id'] ?? null,
                'product_id' => $payload['product_id'] ?? null,
                'subject'    => $payload['subject'] ?? 'تقييم عام',
            ],
            deepLink: 'app://feedback',
            fallback: url('/feedback'),
            feedbackKey: 'feedback_submitted',
            feedbackMsg: 'تم إرسال تقييمك، شكراً جزيلاً! ⭐',
        );
    }

    private function resolveInviteFriend(array $payload): array
    {
        return $this->build(
            type: 'invite_friend',
            payload: [
                'referral_code'    => $payload['referral_code'] ?? null,
                'share_message'    => $payload['share_message'] ?? null,
                'reward_points'    => $payload['reward_points'] ?? 0,
            ],
            deepLink: 'app://invite',
            fallback: url('/invite'),
            feedbackKey: 'invite_shared',
            feedbackMsg: 'تم مشاركة الدعوة بنجاح! 🎁',
        );
    }

    private function resolveLoyaltyPoints(array $payload): array
    {
        return $this->build(
            type: 'loyalty_points',
            payload: [
                'points'     => $payload['points'] ?? null,
                'action'     => $payload['action'] ?? 'view', // view|redeem|earn
                'product_id' => $payload['product_id'] ?? null,
            ],
            deepLink: 'app://loyalty',
            fallback: url('/loyalty'),
            feedbackKey: 'loyalty_action_done',
            feedbackMsg: 'تم تحديث نقاطك! 🏆',
        );
    }

    private function resolveApiPost(array $payload): array
    {
        return $this->build(
            type: 'api_post',
            payload: [
                'endpoint'   => $payload['endpoint'] ?? null,
                'method'     => strtoupper($payload['method'] ?? 'POST'),
                'body'       => $payload['body'] ?? [],
                'headers'    => $payload['headers'] ?? [],
                'auth'       => $payload['auth'] ?? true,  // هل يرسل token المستخدم؟
            ],
            deepLink: null,
            fallback: null,
            feedbackKey: 'api_post_success',
            feedbackMsg: $payload['success_message'] ?? 'تم التنفيذ بنجاح',
        );
    }

    private function resolveApplyCoupon(array $payload): array
    {
        $code = strtoupper($payload['code'] ?? '');

        return $this->build(
            type: 'apply_coupon',
            payload: [
                'code'        => $code,
                'auto_apply'  => $payload['auto_apply'] ?? true, // تطبيق تلقائي في السلة؟
            ],
            deepLink: "app://cart?coupon={$code}",
            fallback: url("/cart?coupon={$code}"),
            feedbackKey: 'coupon_applied',
            feedbackMsg: "تم تطبيق كوبون الخصم {$code} بنجاح! 🎟️",
        );
    }

    private function resolveCopyToClipboard(array $payload): array
    {
        $text = $payload['text'] ?? $payload['value'] ?? '';

        return $this->build(
            type: 'copy_to_clipboard',
            payload: [
                'text'  => $text,
                'label' => $payload['label'] ?? 'النص',
            ],
            deepLink: null,
            fallback: null,
            feedbackKey: 'clipboard_copied',
            feedbackMsg: "تم النسخ إلى الحافظة! 📋",
        );
    }

    private function resolveNotifyAdmin(array $payload): array
    {
        return $this->build(
            type: 'notify_admin',
            payload: [
                'subject'  => $payload['subject'] ?? 'إشعار من مستخدم',
                'body'     => $payload['body'] ?? null,
                'metadata' => $payload['metadata'] ?? [],
            ],
            deepLink: null,
            fallback: null,
            feedbackKey: 'admin_notified',
            feedbackMsg: 'تم إرسال الإشعار للإدارة بنجاح',
        );
    }

    /*──────────────────────────────────────────────────────────────
     | 8. وسائط خارجية
     ──────────────────────────────────────────────────────────────*/

    private function resolveExternalUrl(array $payload): array
    {
        $url = $payload['url'] ?? $payload['target'] ?? null;

        return $this->build(
            type: 'external_url',
            payload: ['url' => $url],
            deepLink: null,
            fallback: $url,
            feedbackKey: 'external_url_opened',
            feedbackMsg: 'تم فتح الرابط',
        );
    }

    private function resolveInAppBrowser(array $payload): array
    {
        $url = $payload['url'] ?? $payload['target'] ?? null;

        return $this->build(
            type: 'inapp_browser',
            payload: [
                'url'              => $url,
                'title'            => $payload['title'] ?? null,
                'show_toolbar'     => $payload['show_toolbar'] ?? true,
            ],
            deepLink: null,
            fallback: $url,
            feedbackKey: 'inapp_browser_opened',
            feedbackMsg: 'تم فتح المتصفح الداخلي',
        );
    }

    private function resolveAppStoreRate(array $payload): array
    {
        $androidId  = $payload['android_package'] ?? config('smartads.android_package');
        $iosId      = $payload['ios_app_id']      ?? config('smartads.ios_app_id');

        $androidDeepLink = "market://details?id={$androidId}";
        $iosDeepLink     = "itms-apps://itunes.apple.com/app/id{$iosId}?action=write-review";

        return $this->build(
            type: 'app_store_rate',
            payload: [
                'android_package'  => $androidId,
                'ios_app_id'       => $iosId,
                'android_deep_link'=> $androidDeepLink,
                'ios_deep_link'    => $iosDeepLink,
                'android_fallback' => "https://play.google.com/store/apps/details?id={$androidId}",
                'ios_fallback'     => "https://apps.apple.com/app/id{$iosId}",
            ],
            deepLink: null, // Flutter يختار الرابط المناسب بناءً على المنصة
            fallback: null,
            feedbackKey: 'app_rated',
            feedbackMsg: 'شكراً على تقييمك! ⭐⭐⭐⭐⭐',
        );
    }

    /*──────────────────────────────────────────────────────────────
     | 9. Helpers الداخلية
     ──────────────────────────────────────────────────────────────*/

    /**
     * بناء الـ response الموحد.
     */
    private function build(
        string  $type,
        array   $payload,
        ?string $deepLink,
        ?string $fallback,
        string  $feedbackKey,
        string  $feedbackMsg,
    ): array {
        return [
            'action_type'  => $type,
            'payload'      => $payload,
            'deep_link'    => $deepLink,
            'fallback_url' => $fallback,
            'feedback'     => [
                'key'     => $feedbackKey,
                'message' => $feedbackMsg,
                'show_snackbar' => true,
                'duration_ms'   => 3000,
            ],
            'tracking' => [
                'click_endpoint'      => url('/api/v1/smartads/track-click'),
                'impression_endpoint' => url('/api/v1/smartads/track-impression'),
            ],
        ];
    }

    /**
     * payload فارغ عند غياب action_data.
     */
    private function empty(): array
    {
        return [
            'action_type'  => 'none',
            'payload'      => [],
            'deep_link'    => null,
            'fallback_url' => null,
            'feedback'     => ['key' => 'none', 'message' => '', 'show_snackbar' => false, 'duration_ms' => 0],
            'tracking'     => [
                'click_endpoint'      => url('/api/v1/smartads/track-click'),
                'impression_endpoint' => url('/api/v1/smartads/track-impression'),
            ],
        ];
    }

    /*──────────────────────────────────────────────────────────────
     | 10. قائمة بجميع أنواع الإجراءات المدعومة (للـ Admin UI)
     ──────────────────────────────────────────────────────────────*/

    /**
     * أنواع الإجراءات المدعومة مُجمّعة حسب الفئة.
     * يُستخدم في واجهة الإدارة لبناء الـ dropdown.
     */
    public static function supportedTypes(): array
    {
        return [
            'internal_navigation' => [
                'product'          => 'منتج محدد',
                'category'         => 'قسم/تصنيف',
                'brand'            => 'علامة تجارية',
                'deals'            => 'صفحة العروض',
                'wallet'           => 'المحفظة',
                'order_tracking'   => 'تتبع الطلبات',
                'account_settings' => 'إعدادات الحساب',
            ],
            'social_follow' => [
                'facebook_follow'   => 'متابعة Facebook',
                'instagram_follow'  => 'متابعة Instagram',
                'tiktok_follow'     => 'متابعة TikTok',
                'x_follow'          => 'متابعة X (Twitter)',
                'telegram_join'     => 'الانضمام لـ Telegram',
                'youtube_subscribe' => 'الاشتراك في YouTube',
            ],
            'social_interact' => [
                'facebook_like_post'  => 'إعجاب بمنشور Facebook',
                'instagram_like_post' => 'إعجاب بمنشور Instagram',
                'telegram_view_post'  => 'عرض منشور Telegram',
                'whatsapp_chat'       => 'محادثة WhatsApp',
            ],
            'contact' => [
                'save_contact' => 'حفظ جهة الاتصال',
            ],
            'interactive' => [
                'survey'            => 'استبيان',
                'feedback'          => 'تقييم/رأي',
                'invite_friend'     => 'دعوة صديق',
                'loyalty_points'    => 'نقاط الولاء',
                'api_post'          => 'إرسال طلب API',
                'apply_coupon'      => 'تطبيق كوبون',
                'copy_to_clipboard' => 'نسخ للحافظة',
                'notify_admin'      => 'إشعار الإدارة',
            ],
            'external_media' => [
                'external_url'   => 'رابط خارجي (متصفح افتراضي)',
                'inapp_browser'  => 'رابط داخل التطبيق',
                'app_store_rate' => 'تقييم في المتجر',
            ],
        ];
    }

    /**
     * الحقول المطلوبة لكل نوع إجراء (للـ dynamic form في الإدارة).
     */
    public static function requiredFields(string $type): array
    {
        return match ($type) {
            'product'               => ['id', 'slug'],
            'category'              => ['id', 'slug'],
            'brand'                 => ['id', 'slug'],
            'deals'                 => ['filter'],
            'wallet'                => ['tab'],
            'order_tracking'        => ['order_id'],
            'account_settings'      => ['section'],

            'facebook_follow',
            'instagram_follow',
            'tiktok_follow',
            'x_follow',
            'telegram_join',
            'youtube_subscribe'     => ['profile_id', 'profile_url'],

            'facebook_like_post',
            'instagram_like_post'   => ['post_id', 'post_url'],
            'telegram_view_post'    => ['channel_id', 'post_id', 'post_url'],

            'whatsapp_chat'         => ['phone', 'message'],
            'save_contact'          => ['name', 'phone', 'company'],

            'survey'                => ['survey_url'],
            'feedback'              => [],
            'invite_friend'         => ['referral_code'],
            'loyalty_points'        => ['action'],
            'api_post'              => ['endpoint', 'method'],
            'apply_coupon'          => ['code'],
            'copy_to_clipboard'     => ['text'],
            'notify_admin'          => ['subject'],

            'external_url',
            'inapp_browser'         => ['url'],
            'app_store_rate'        => ['android_package', 'ios_app_id'],
            default                 => [],
        };
    }
}
