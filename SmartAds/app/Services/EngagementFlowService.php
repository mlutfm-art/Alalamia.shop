<?php

namespace Modules\SmartAds\app\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\SmartAds\app\Models\SmartAd;
use Modules\SmartAds\app\Models\SmartAdNotification;

class EngagementFlowService
{
    public function __construct(
        private readonly ActionResolverService  $resolver,
        private readonly FlutterResponseBuilder $builder,
    ) {}

    public function trigger(SmartAd $ad, array $options = []): array
    {
        $results = [
            'push'        => null,
            'notification' => null,
            'banner'      => null,
        ];

        try {
            $resolved = $this->resolver->resolve($ad->action_data);
            $flutterPayload = $this->builder->build($ad, $resolved);

            if ($options['send_push'] ?? true) {
                $results['push'] = $this->sendFirebasePush($ad, $flutterPayload, $options);
            }

            $results['notification'] = $this->createNotificationRecord($ad, $flutterPayload);
            $results['banner'] = $this->prepareBannerData($ad, $flutterPayload);

        } catch (\Throwable $e) {
            Log::error('[EngagementFlowService] trigger failed', [
                'ad_id' => $ad->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return $results;
    }

    private function sendFirebasePush(SmartAd $ad, array $flutterPayload, array $options): array
    {
        $topic   = $options['topic']    ?? 'all_users';
        $userIds = $options['user_ids'] ?? [];

        $notification = [
            'title' => $ad->title,
            'body'  => $ad->action_data['subtitle'] ?? $ad->action_data['description'] ?? '',
            'image' => $ad->image_url,
        ];

        $data = [
            'type'        => 'smart_ad',
            'ad_id'       => (string) $ad->id,
            'action_type' => $flutterPayload['action']['action_type'],
            'payload'     => json_encode($flutterPayload['action']['payload']),
            'deep_link'   => $flutterPayload['action']['deep_link'] ?? '',
            'image'       => $ad->image_url ?? '',
            'ad_type'     => $ad->ad_type,
        ];

        $sent = 0;
        $failed = 0;

        try {
            if (!empty($userIds)) {
                $tokens = [];
                foreach ($userIds as $userId) {
                    $token = $this->getUserFcmToken($userId);
                    if ($token) {
                        $tokens[] = $token;
                    }
                }
                if (!empty($tokens)) {
                    $this->sendFcmToDevices($tokens, $notification, $data);
                    $sent = count($tokens);
                }
            } else {
                $success = $this->sendFcmToTopic($topic, $notification, $data);
                $success ? $sent++ : $failed++;
            }
        } catch (\Throwable $e) {
            Log::error('[EngagementFlowService] FCM send error', ['error' => $e->getMessage()]);
        }

        return [
            'sent'   => $sent,
            'failed' => $failed,
            'topic'  => $topic,
        ];
    }

    private function sendFcmToTopic(string $topic, array $notification, array $data): bool
    {
        try {
            $key = (array) getWebConfig('push_notification_key');
            if (!isset($key['project_id'])) {
                Log::warning('[SmartAds] push_notification_key not configured');
                return false;
            }

            $accessToken = $this->getAccessToken($key);
            if (!$accessToken) {
                return false;
            }

            $postData = [
                'message' => [
                    'topic' => $topic,
                    'data' => [
                        'title' => (string) ($notification['title'] ?? ''),
                        'body' => (string) ($notification['body'] ?? ''),
                        'image' => (string) ($notification['image'] ?? ''),
                        'type' => (string) ($data['type'] ?? 'smart_ad'),
                        'ad_id' => (string) ($data['ad_id'] ?? ''),
                        'action_type' => (string) ($data['action_type'] ?? ''),
                        'payload' => (string) ($data['payload'] ?? ''),
                        'deep_link' => (string) ($data['deep_link'] ?? ''),
                        'is_read' => '0'
                    ],
                    'notification' => [
                        'title' => (string) $notification['title'],
                        'body' => (string) $notification['body'],
                    ],
                    'apns' => [
                        'payload' => [
                            'aps' => ['sound' => 'default']
                        ]
                    ]
                ]
            ];

            $url = 'https://fcm.googleapis.com/v1/projects/' . $key['project_id'] . '/messages:send';
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->post($url, $postData);

            return $response->successful();

        } catch (\Exception $e) {
            Log::error('[SmartAds] sendFcmToTopic error: ' . $e->getMessage());
            return false;
        }
    }

    private function sendFcmToToken(string $token, array $notification, array $data): bool
    {
        try {
            $key = (array) getWebConfig('push_notification_key');
            if (!isset($key['project_id'])) {
                return false;
            }

            $accessToken = $this->getAccessToken($key);
            if (!$accessToken) {
                return false;
            }

            $postData = [
                'message' => [
                    'token' => $token,
                    'data' => [
                        'title' => (string) ($notification['title'] ?? ''),
                        'body' => (string) ($notification['body'] ?? ''),
                        'image' => (string) ($notification['image'] ?? ''),
                        'type' => (string) ($data['type'] ?? 'smart_ad'),
                        'ad_id' => (string) ($data['ad_id'] ?? ''),
                        'action_type' => (string) ($data['action_type'] ?? ''),
                        'payload' => (string) ($data['payload'] ?? ''),
                        'deep_link' => (string) ($data['deep_link'] ?? ''),
                        'is_read' => '0'
                    ],
                    'notification' => [
                        'title' => (string) $notification['title'],
                        'body' => (string) $notification['body'],
                    ],
                    'apns' => [
                        'payload' => [
                            'aps' => ['sound' => 'default']
                        ]
                    ]
                ]
            ];

            $url = 'https://fcm.googleapis.com/v1/projects/' . $key['project_id'] . '/messages:send';
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->post($url, $postData);

            return $response->successful();

        } catch (\Exception $e) {
            Log::error('[SmartAds] sendFcmToToken error: ' . $e->getMessage());
            return false;
        }
    }

    private function sendFcmToDevices(array $tokens, array $notification, array $data): void
    {
        foreach ($tokens as $token) {
            $this->sendFcmToToken($token, $notification, $data);
        }
    }

    private function getAccessToken(array $key): ?string
    {
        try {
            $jwtToken = [
                'iss' => $key['client_email'],
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud' => 'https://oauth2.googleapis.com/token',
                'exp' => time() + 3600,
                'iat' => time(),
            ];

            $jwtHeader = base64_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
            $jwtPayload = base64_encode(json_encode($jwtToken));
            $unsignedJwt = $jwtHeader . '.' . $jwtPayload;

            openssl_sign($unsignedJwt, $signature, $key['private_key'], OPENSSL_ALGO_SHA256);
            $jwt = $unsignedJwt . '.' . base64_encode($signature);

            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]);

            return $response->json('access_token') ?? null;

        } catch (\Exception $e) {
            Log::error('[SmartAds] getAccessToken error: ' . $e->getMessage());
            return null;
        }
    }

    private function getUserFcmToken(int $userId): ?string
    {
        return DB::table('users')
            ->where('id', $userId)
            ->value('cm_firebase_token')
            ?? DB::table('user_fcm_tokens')
                ->where('user_id', $userId)
                ->orderByDesc('created_at')
                ->value('token');
    }

    private function createNotificationRecord(SmartAd $ad, array $flutterPayload): SmartAdNotification
    {
        return SmartAdNotification::create([
            'smart_ad_id'    => $ad->id,
            'title'          => $ad->title,
            'body'           => $ad->action_data['subtitle'] ?? $ad->action_data['description'] ?? '',
            'image_url'      => $ad->image_url,
            'action_type'    => $flutterPayload['action']['action_type'],
            'flutter_payload' => $flutterPayload,
            'display_type'   => $this->resolveDisplayType($ad),
            'is_read'        => false,
            'expires_at'     => $ad->end_at,
        ]);
    }

    private function prepareBannerData(SmartAd $ad, array $flutterPayload): array
    {
        return [
            'enabled'          => true,
            'ad_id'            => $ad->id,
            'title'            => $ad->title,
            'subtitle'         => $ad->action_data['subtitle'] ?? '',
            'description'      => $ad->action_data['description'] ?? '',
            'image_url'        => $ad->image_url,
            'video_url'        => $ad->video_url,
            'button_text'      => $ad->action_data['button_text'] ?? 'اعرف أكثر',
            'background_color' => $ad->action_data['background_color'] ?? '#FFFFFF',
            'text_color'       => $ad->action_data['text_color'] ?? '#000000',
            'display_duration_ms' => config('smartads.banner_duration_ms', 5000),
            'dismissible'      => true,
            'action'           => $flutterPayload['action'],
        ];
    }

    private function resolveDisplayType(SmartAd $ad): string
    {
        return match ($ad->ad_type) {
            'notification' => 'push',
            'popup'        => 'inapp_banner',
            'banner'       => 'inapp_banner',
            default        => 'notification_center',
        };
    }
}