<?php

namespace Modules\SmartAds\app\Services;

use Modules\SmartAds\app\Models\SmartAd;

/**
 * ╔══════════════════════════════════════════════════════════════════╗
 * ║         FlutterResponseBuilder — بناء استجابة Flutter الموحدة  ║
 * ║                                                                  ║
 * ║  يجمع بيانات الإعلان + resolved action + metadata الشاملة      ║
 * ║  في هيكل JSON واحد يفهمه ActionHandler في Flutter.             ║
 * ╚══════════════════════════════════════════════════════════════════╝
 *
 * هيكل الـ Response النهائي لـ Flutter:
 * {
 *   "id": 1,
 *   "title": "...",
 *   "ad_type": "banner",
 *   "image_url": "...",
 *   "video_url": null,
 *   "display": {
 *     "type": "inapp_banner",      // push | inapp_banner | notification_center
 *     "duration_ms": 5000,
 *     "dismissible": true,
 *     "button_text": "اشترك الآن",
 *     "background_color": "#FF5722",
 *     "text_color": "#FFFFFF"
 *   },
 *   "action": {
 *     "action_type": "instagram_follow",
 *     "payload": { "platform": "instagram", "profile_id": "brandname", ... },
 *     "deep_link": "instagram://user?username=brandname",
 *     "fallback_url": "https://instagram.com/brandname",
 *     "feedback": { "key": "social_follow_success", "message": "تمت المتابعة! 🎉", ... },
 *     "tracking": { "click_endpoint": "...", "impression_endpoint": "..." }
 *   },
 *   "targeting": {
 *     "device_type": "all",
 *     "region": null,
 *     "category_id": null
 *   },
 *   "schedule": {
 *     "start_at": "2026-06-21T00:00:00Z",
 *     "end_at": null
 *   },
 *   "ab_variant": null,
 *   "root_id": 1
 * }
 */
class FlutterResponseBuilder
{
    /**
     * بناء الـ response الكامل لإعلان واحد.
     *
     * @param  SmartAd  $ad
     * @param  array    $resolvedAction  مخرجات ActionResolverService::resolve()
     * @param  int|null $rootId          معرف الإعلان الأب (A/B testing)
     * @return array
     */
    public function build(SmartAd $ad, array $resolvedAction, ?int $rootId = null): array
    {
        return [
            'id'        => $ad->id,
            'root_id'   => $rootId ?? $ad->id,
            'title'     => $ad->title,
            'ad_type'   => $ad->ad_type,
            'placement' => $ad->placement,
            'image_url' => $ad->image_url,
            'video_url' => $ad->ad_type === 'video' ? $ad->video_url : null,

            // ── عرض البانر/الإشعار ──────────────────────────────────
            'display'   => $this->buildDisplay($ad),

            // ── الإجراء المُحلول (قلب الـ Engine) ───────────────────
            'action'    => $resolvedAction,

            // ── الاستهداف ───────────────────────────────────────────
            'targeting' => [
                'device_type' => $ad->device_type,
                'region'      => $ad->target_region,
                'category_id' => $ad->target_category_id,
            ],

            // ── الجدولة ─────────────────────────────────────────────
            'schedule'  => [
                'start_at' => $ad->start_at?->toISOString(),
                'end_at'   => $ad->end_at?->toISOString(),
            ],

            // ── A/B testing ─────────────────────────────────────────
            'ab_variant' => $ad->ab_variant,

            // ── meta للتتبع ─────────────────────────────────────────
            'meta' => [
                'impressions' => $ad->impressions,
                'clicks'      => $ad->clicks,
                'ctr'         => $ad->ctr,
            ],
        ];
    }

    /**
     * بناء معلومات العرض (display metadata).
     */
    private function buildDisplay(SmartAd $ad): array
    {
        $actionData = $ad->action_data ?? [];

        return [
            'type'             => $this->mapDisplayType($ad->ad_type),
            'duration_ms'      => (int) config('smartads.banner_duration_ms', 5000),
            'dismissible'      => true,
            'button_text'      => $actionData['button_text']      ?? 'اعرف أكثر',
            'subtitle'         => $actionData['subtitle']          ?? '',
            'description'      => $actionData['description']       ?? '',
            'background_color' => $actionData['background_color']  ?? '#FFFFFF',
            'text_color'       => $actionData['text_color']        ?? '#212121',
            'overlay_opacity'  => $this->resolveOverlayOpacity($ad->ad_type),
            'position'         => $this->resolvePosition($ad->placement),
        ];
    }

    /**
     * ربط ad_type بنوع العرض في Flutter.
     */
    private function mapDisplayType(string $adType): string
    {
        return match ($adType) {
            'notification' => 'push',
            'popup'        => 'inapp_banner',
            'banner'       => 'inapp_banner',
            'native'       => 'native_card',
            'video'        => 'video_banner',
            default        => 'notification_center',
        };
    }

    /**
     * درجة شفافية الـ overlay حسب نوع الإعلان.
     */
    private function resolveOverlayOpacity(string $adType): float
    {
        return match ($adType) {
            'popup'  => 0.7,
            'banner' => 0.0,
            'video'  => 0.0,
            default  => 0.0,
        };
    }

    /**
     * موضع ظهور البانر حسب الـ placement.
     */
    private function resolvePosition(string $placement): string
    {
        return match (true) {
            str_contains($placement, 'top')    => 'top',
            str_contains($placement, 'bottom') => 'bottom',
            str_contains($placement, 'center') => 'center',
            default                            => 'bottom',
        };
    }

    /**
     * بناء قائمة إعلانات (للـ API endpoint الذي يُرجع مجموعة).
     */
    public function buildList(iterable $ads, ActionResolverService $resolver): array
    {
        return collect($ads)->map(function (SmartAd $ad) use ($resolver) {
            $resolved = $resolver->resolve($ad->action_data);
            return $this->build($ad, $resolved, $ad->id);
        })->values()->all();
    }
}
