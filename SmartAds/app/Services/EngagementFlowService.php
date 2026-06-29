<?php

namespace Modules\SmartAds\app\Services;

use Illuminate\Support\Facades\Log;
use Modules\SmartAds\app\Models\SmartAd;
use Modules\SmartAds\app\Models\SmartAdNotification;
use Modules\SmartAds\app\Models\DeviceToken;

class EngagementFlowService
{
    public function __construct(
        private readonly ActionResolverService $resolver,
        private readonly FCMService $fcmService
    ) {}

    /**
     * إطلاق الحملة التسويقية: إرسال Push + حفظ في Notification Center
     */
    public function trigger(SmartAd $ad, array $options = []): array
    {
        try {
            // 1. حل الإجراء (Link/Product/Category)
            $resolved = $this->resolver->resolve($ad->action_data);

            $title = $ad->title;
            $body = $ad->sub_title ?? 'اكتشف العروض الجديدة الآن!';
            
            // 2. بناء الـ Payload الموحد
            $dataPayload = [
                'type'             => 'smart_ad_enterprise',
                'ad_id'            => (string)$ad->id,
                'action_type'      => (string)$resolved['action_type'],
                'payload'          => json_encode($resolved['payload']),
                'deep_link'        => (string)($resolved['deep_link'] ?? ''),
                'image'            => $ad->image_url ?? '',
            ];

            // 3. الإرسال اللحظي لجميع الأجهزة
            $fcmResult = $this->fcmService->sendToAll($title, $body, $dataPayload);

            // 4. حفظ الإشعار في قاعدة البيانات (ليظهر في تطبيق الموبايل تحت أيقونة الجرس)
            $this->saveToNotificationCenter($ad, $resolved, $title, $body);

            if ($fcmResult['success'] > 0) {
                $ad->increment('sent_count', $fcmResult['success']);
            }

            return $fcmResult;

        } catch (\Throwable $e) {
            Log::error("[SmartAds] Trigger Failed: " . $e->getMessage());
            return ['success' => 0, 'failure' => 1, 'error' => $e->getMessage()];
        }
    }

    /**
     * حفظ الإشعار في موديول SmartAds ليكون متاحاً عبر الـ API
     */
    private function saveToNotificationCenter(SmartAd $ad, array $resolved, $title, $body)
    {
        try {
            SmartAdNotification::create([
                'smart_ad_id'     => $ad->id,
                'title'           => $title,
                'body'            => $body,
                'image_url'       => $ad->image_url,
                'action_type'     => $resolved['action_type'],
                'flutter_payload' => $resolved,
                'display_type'    => 'notification_center',
                'is_read'         => false,
            ]);
        } catch (\Exception $e) {
            Log::warning("[SmartAds] Could not save to notification center: " . $e->getMessage());
        }
    }
}
