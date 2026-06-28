<?php
namespace Modules\Predictions\app\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Modules\Predictions\app\Events\BannerActivated;
use Modules\Predictions\app\Notifications\BannerNotification;

class NotifyCustomersBannerActivated implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries   = 2;
    public int $timeout = 120;

    public function handle(BannerActivated $event): void
    {
        $title = $event->title;
        $body  = $event->description ?: $event->title;
        $type  = 'banner_' . $event->triggerType;

        /* ── 1. Database notifications (Laravel standard) ── */
        $this->sendDatabaseNotifications($title, $body, $event->image, $event->matchId, $type);

        /* ── 2. FCM push via 6Valley helper (optional, fails silently) ── */
        $this->sendFcmPush($title, $body, $event->matchId, $type);
    }

    private function sendDatabaseNotifications(
        string $title, string $body, string $image, ?int $matchId, string $type
    ): void {
        $notification = new BannerNotification($title, $body, $image, $matchId, $type);

        try {
            $model = $this->resolveCustomerModel();
            if (!$model) return;

            $model::query()->select('id')
                ->chunk(200, function ($customers) use ($notification) {
                    foreach ($customers as $customer) {
                        try {
                            $customer->notify($notification);
                        } catch (\Throwable $e) {
                            \Log::warning('[Predictions/Banner] notify single customer failed: ' . $e->getMessage());
                        }
                    }
                });
        } catch (\Throwable $e) {
            \Log::warning('[Predictions/Banner] database notifications failed: ' . $e->getMessage());
        }
    }

    private function sendFcmPush(string $title, string $body, ?int $matchId, string $type): void
    {
        $data = [
            'type'         => $type,
            'match_id'     => (string)($matchId ?? ''),
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
        ];

        try {
            /* Try 6Valley's built-in FCM helper (same as PredictionMatchOpened listener) */
            if (class_exists('\App\CentralLogics\Helpers')) {
                \App\CentralLogics\Helpers::send_push_notif_to_topic(
                    $data,
                    'customers',
                    'general',
                    $title,
                    $body
                );
                return;
            }
        } catch (\Throwable $e) {
            \Log::info('[Predictions/Banner] FCM topic push failed (non-critical): ' . $e->getMessage());
        }

        /* Fallback: send per-token if token table exists */
        try {
            $tokens = \DB::table('customer_fcm_tokens')->pluck('token');
            if ($tokens->isEmpty()) return;

            $serverKey = config('fcm.server_key', env('FCM_SERVER_KEY', ''));
            if (!$serverKey) return;

            foreach ($tokens->chunk(500) as $chunk) {
                $this->sendFcmBatch($serverKey, $chunk->values()->all(), $title, $body, $data);
            }
        } catch (\Throwable $e) {
            \Log::info('[Predictions/Banner] FCM per-token push failed (non-critical): ' . $e->getMessage());
        }
    }

    private function sendFcmBatch(string $key, array $tokens, string $title, string $body, array $data): void
    {
        $payload = json_encode([
            'registration_ids' => $tokens,
            'notification'     => ['title' => $title, 'body' => $body, 'sound' => 'default'],
            'data'             => $data,
            'priority'         => 'high',
        ]);

        $ch = curl_init('https://fcm.googleapis.com/fcm/send');
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTPHEADER     => [
                'Authorization: key=' . $key,
                'Content-Type: application/json',
            ],
        ]);
        curl_exec($ch);
        curl_close($ch);
    }

    private function resolveCustomerModel(): ?string
    {
        foreach (['App\Models\Customer', 'App\Models\User', 'App\User'] as $class) {
            if (class_exists($class)) return $class;
        }
        return null;
    }
}
