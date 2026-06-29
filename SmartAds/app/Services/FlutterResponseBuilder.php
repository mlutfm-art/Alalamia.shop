<?php

namespace Modules\SmartAds\app\Services;

use Modules\SmartAds\app\Models\SmartAd;

class FlutterResponseBuilder
{
    public function build(SmartAd $ad, array $resolvedAction, ?int $rootId = null): array
    {
        return [
            'id'            => $ad->id,
            'root_id'       => $rootId ?? $ad->id,
            'title'         => $ad->title,
            'sub_title'     => $ad->sub_title,
            'ad_type'       => $ad->ad_type,
            'placement'     => $ad->placement,
            'image_url'     => $ad->image_url,
            'video_url'     => $ad->video_url,
            'is_ai'         => (bool)$ad->is_ai_generated,
            
            // 🚀 محرك التفاعل (Action Engine)
            'action_engine' => [
                'type'             => $resolvedAction['action_type'],
                'payload'          => $resolvedAction['payload'],
                'deep_link'        => $resolvedAction['deep_link'],
                'fallback_url'     => $resolvedAction['fallback_url'] ?? '',
                'feedback_message' => $resolvedAction['feedback']['message'] ?? '',
            ],

            // 🎨 تم التغيير ليتوافق مع Flutter Model (display_settings)
            'display_settings' => [
                'background_color' => $ad->action_data['background_color'] ?? '#FFFFFF',
                'text_color'       => $ad->action_data['text_color'] ?? '#000000',
                'button_text'      => $ad->button_text ?? 'Learn More',
                'subtitle'         => $ad->sub_title,
                'duration_ms'      => 5000,
            ],

            'dynamic_context' => $ad->dynamic_context ?? [],

            'tracking' => [
                'click_url'      => url("/api/v1/smartads/track-click/{$ad->id}"),
                'impression_url' => url("/api/v1/smartads/track-impression/{$ad->id}"),
            ],
            
            'expires_at' => $ad->end_at ? $ad->end_at->toIso8601String() : null,
        ];
    }
}
