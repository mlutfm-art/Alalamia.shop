<?php
namespace Modules\Predictions\app\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Modules\Predictions\app\Events\PredictionMatchOpened;
use Modules\Predictions\app\Notifications\PredictionOpenedNotification;

class NotifyCustomersPredictionOpened implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries   = 2;
    public int $timeout = 120;

    public function handle(PredictionMatchOpened $event): void
    {
        $match = $event->match;

        /* ── 1. Database notifications (Laravel standard) ── */
        $this->sendDatabaseNotifications($match);

        /* ── 2. FCM push via 6Valley helper (optional, fails silently) ── */
        $this->sendFcmPush($match);
    }

    private function sendDatabaseNotifications($match): void
    {
        $notification = new PredictionOpenedNotification($match);
        $title = '⚽ ' . $match->team1_name . ' vs ' . $match->team2_name;
        $body  = 'توقّع النتيجة واربح ' . $match->reward_points . ' نقطة!';

        try {
            $model = $this->resolveCustomerModel();
            if (!$model) return;

            $model::query()->select('id')
                ->chunk(200, function ($customers) use ($notification) {
                    foreach ($customers as $customer) {
                        try {
                            $customer->notify($notification);
                        } catch (\Throwable $e) {
                            \Log::warning('[Predictions] notify single customer failed: ' . $e->getMessage());
                        }
                    }
                });
        } catch (\Throwable $e) {
            \Log::warning('[Predictions] database notifications failed: ' . $e->getMessage());
        }
    }

    private function sendFcmPush($match): void
    {
        $title = '⚽ ' . $match->team1_name . ' vs ' . $match->team2_name;
        $body  = 'توقّع النتيجة واربح ' . $match->reward_points . ' نقطة!';
        $data  = [
            'type'     => 'prediction_opened',
            'match_id' => (string)$match->id,
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
        ];

        try {
            /* Try 6Valley's built-in FCM helper */
            if (class_exists('\App\CentralLogics\Helpers')) {
                /* Send topic notification to all customers */
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
            \Log::info('[Predictions] FCM topic push failed (non-critical): ' . $e->getMessage());
        }

        /* Fallback: send per-token if token table exists */
        try {
            $tokens = \DB::table('customer_fcm_tokens')->pluck('token');
            if ($tokens->isEmpty()) return;

            $serverKey = config('fcm.server_key', env('FCM_SERVER_KEY',''));
            if (!$serverKey) return;

            foreach ($tokens->chunk(500) as $chunk) {
                $this->sendFcmBatch($serverKey, $chunk->values()->all(), $title, $body, $data);
            }
        } catch (\Throwable $e) {
            \Log::info('[Predictions] FCM per-token push failed (non-critical): ' . $e->getMessage());
        }
    }

    private function sendFcmBatch(string $key, array $tokens, string $title, string $body, array $data): void
    {
        $payload = json_encode([
            'registration_ids' => $tokens,
            'notification' => ['title' => $title, 'body' => $body, 'sound' => 'default'],
            'data' => $data,
            'priority' => 'high',
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

    /** Resolve the customer model used by this 6Valley instance. */
    private function resolveCustomerModel(): ?string
    {
        foreach (['App\Models\Customer', 'App\Models\User', 'App\User'] as $class) {
            if (class_exists($class)) return $class;
        }
        return null;
    }
}
