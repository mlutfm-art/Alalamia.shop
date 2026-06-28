<?php

namespace Modules\SmartAds\app\Services;

use Modules\SmartAds\app\Models\DeviceToken;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Illuminate\Support\Facades\Log;

class FCMService
{
    protected $messaging;

    public function __construct()
    {
        $credentialsFile = config('smartads-fcm.credentials_file');

        if (!file_exists($credentialsFile)) {
            throw new \RuntimeException(
                '[SmartAds FCM] Service Account file not found: ' . $credentialsFile .
                ' — ضع ملف smartads-service-account.json في storage/app/'
            );
        }

        $factory = (new Factory)->withServiceAccount($credentialsFile);
        $this->messaging = $factory->createMessaging();
    }

    /**
     * إرسال لتوكن واحد
     */
    public function sendToToken(string $token, string $title, string $body, array $data = []): array
    {
        try {
            $message = CloudMessage::withTarget('token', $token)
                ->withNotification(Notification::create($title, $body))
                ->withData(array_map('strval', $data));

            $this->messaging->send($message);

            return ['success' => 1, 'failure' => 0];

        } catch (\Kreait\Firebase\Exception\Messaging\NotFound $e) {
            DeviceToken::where('token', $token)->delete();
            return ['success' => 0, 'failure' => 1, 'error' => 'Token expired and removed'];

        } catch (\Throwable $e) {
            Log::error('[SmartAds FCM] sendToToken: ' . $e->getMessage());
            return ['success' => 0, 'failure' => 1, 'error' => $e->getMessage()];
        }
    }

    /**
     * إرسال لعدة توكنات دفعة (max 500 per batch)
     */
    public function sendToMultiple(array $tokens, string $title, string $body, array $data = []): array
    {
        if (empty($tokens)) {
            return ['success' => 0, 'failure' => 0];
        }

        $results = ['success' => 0, 'failure' => 0];

        foreach (array_chunk($tokens, 500) as $chunk) {
            $messages = array_map(
                fn($t) => CloudMessage::withTarget('token', $t)
                    ->withNotification(Notification::create($title, $body))
                    ->withData(array_map('strval', $data)),
                $chunk
            );

            try {
                $report = $this->messaging->sendAll($messages);

                $results['success'] += $report->successes()->count();
                $results['failure'] += $report->failures()->count();

                foreach ($report->failures()->getItems() as $failure) {
                    DeviceToken::where('token', $failure->target()->value())->delete();
                }
            } catch (\Throwable $e) {
                Log::error('[SmartAds FCM] sendToMultiple batch: ' . $e->getMessage());
                $results['failure'] += count($chunk);
            }
        }

        return $results;
    }

    /**
     * إرسال لجميع الأجهزة المسجلة
     */
    public function sendToAll(string $title, string $body, array $data = []): array
    {
        $tokens = DeviceToken::pluck('token')->toArray();

        if (empty($tokens)) {
            return ['success' => 0, 'failure' => 0, 'message' => 'No registered devices'];
        }

        return $this->sendToMultiple($tokens, $title, $body, $data);
    }

    /**
     * إرسال لمستخدم محدد (كل أجهزته)
     */
    public function sendToUser(int $userId, string $userType, string $title, string $body, array $data = []): array
    {
        $tokens = DeviceToken::where('tokenable_id', $userId)
            ->where('tokenable_type', $userType)
            ->pluck('token')
            ->toArray();

        return $this->sendToMultiple($tokens, $title, $body, $data);
    }
}
