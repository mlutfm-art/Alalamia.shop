<?php

namespace Modules\SmartAds\app\Services;

use Modules\SmartAds\app\Models\DeviceToken;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;
use Illuminate\Support\Facades\Log;

class FCMService
{
    protected $messaging;

    public function __construct()
    {
        $credentialsFile = config('smartads-fcm.credentials_file');

        if (!file_exists($credentialsFile)) {
            Log::error('[SmartAds FCM] Missing JSON at: ' . $credentialsFile);
            throw new \RuntimeException('ملف Firebase JSON غير موجود في المسار: ' . $credentialsFile);
        }

        $factory = (new Factory)->withServiceAccount($credentialsFile);
        $this->messaging = $factory->createMessaging();
    }

    public function sendToAll(string $title, string $body, array $data = []): array
    {
        $tokens = DeviceToken::pluck('token')->unique()->toArray();
        
        if (empty($tokens)) {
            return ['success' => 0, 'failure' => 0, 'message' => 'لا يوجد أجهزة مسجلة في جدول device_tokens'];
        }

        $success = 0;
        $failure = 0;

        foreach ($tokens as $token) {
            try {
                $message = CloudMessage::withTarget('token', $token)
                    ->withNotification(FirebaseNotification::create($title, $body))
                    ->withData(array_map('strval', $data))
                    ->withAndroidConfig([
                        'priority' => 'high',
                        'notification' => [
                            'channel_id' => 'smartads_channel', // يجب أن يطابق ما في Flutter
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                            'sound' => 'default'
                        ]
                    ]);

                $this->messaging->send($message);
                $success++;
            } catch (\Exception $e) {
                $failure++;
                if (str_contains($e->getMessage(), 'Requested entity was not found')) {
                    DeviceToken::where('token', $token)->delete();
                }
            }
        }

        return ['success' => $success, 'failure' => $failure];
    }
}
