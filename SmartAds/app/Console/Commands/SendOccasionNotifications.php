<?php
namespace Modules\SmartAds\app\Console\Commands;
use Illuminate\Console\Command;
use Modules\Alertmarkting\app\Models\NotificationOccasion;
use App\Models\User;
use Modules\SmartAds\app\Services\FCMService;

class SendOccasionNotifications extends Command
{
    protected $signature = 'smartads:send-occasions';
    protected $description = 'إرسال تهاني المناسبات تلقائياً';

    public function handle()
    {
        $today = now()->toDateString();
        $occasions = NotificationOccasion::where('is_active', true)
            ->whereDate('date', '<=', now()->addDays(1))
            ->where('sent_this_year', false)
            ->get();

        if ($occasions->isEmpty()) {
            $this->info('لا توجد مناسبات اليوم.');
            return 0;
        }

        $fcm = new FCMService();
        foreach ($occasions as $o) {
            $shouldSend = false;
            if ($o->send_before_days > 0) {
                $targetDate = now()->addDays($o->send_before_days)->toDateString();
                $shouldSend = ($o->date->toDateString() == $targetDate);
            } else {
                $shouldSend = ($o->date->toDateString() == $today);
            }

            if (!$shouldSend) continue;

            $tokens = User::whereNotNull('cm_firebase_token')
                ->where('cm_firebase_token', '!=', '')
                ->where(function($q) {
                    $q->where('cm_firebase_token', 'not like', 'eBFC%')
                      ->where('cm_firebase_token', 'not like', 'dPz%');
                })
                ->pluck('cm_firebase_token')->toArray();

            if (empty($tokens)) {
                $this->warn("لا توجد توكنات جوال للمناسبة: {$o->name}");
                continue;
            }

            try {
                $result = $fcm->sendToMultiple($tokens, $o->notification_title, $o->notification_body);
                $o->sent_this_year = true;
                $o->save();
                $this->info("✅ تم إرسال: {$o->name} | نجح: " . ($result['success'] ?? 0));
            } catch (\Exception $e) {
                $this->error("❌ فشل: {$o->name} | " . $e->getMessage());
            }
        }
        return 0;
    }
}
