<?php
namespace Modules\SmartAds\app\Console\Commands;
use Illuminate\Console\Command;
use Modules\SmartAds\app\Models\SmartAdSchedule;
use App\Models\User;
use Modules\SmartAds\app\Services\FCMService;
use Modules\Alertmarkting\app\Services\NotificationService;

class SendScheduledAds extends Command
{
    protected $signature = 'smartads:send-scheduled';
    protected $description = 'Send scheduled ads';

    public function handle()
    {
        $schedules = SmartAdSchedule::where('status','pending')->where('scheduled_at','<=',now())->get();
        if($schedules->isEmpty()){ $this->info('No scheduled ads.'); return 0; }
        $service = new NotificationService();
        $fcm = new FCMService();
        foreach($schedules as $s){
            $tokens = [];
            if($s->target_type=='all'){
                $tokens = User::whereNotNull('cm_firebase_token')->where('cm_firebase_token','!=','')->pluck('cm_firebase_token')->toArray();
            } else {
                $query = $service->buildTargetQuery($s->target_type, $s->target_value??[], true);
                $tokens = $query->pluck('cm_firebase_token')->toArray();
            }
            if(empty($tokens)){ $s->update(['status'=>'failed']); continue; }
            try {
                $fcm->sendToMultiple($tokens, $s->title, $s->body);
                $s->update(['status'=>'sent']);
                $this->info("Sent: {$s->title}");
            } catch(\Exception $e){
                $s->update(['status'=>'failed']);
                $this->error("Failed: {$s->title}");
            }
        }
        return 0;
    }
}
