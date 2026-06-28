<?php
namespace Modules\Predictions\app\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Modules\Predictions\app\Models\PredictionMatch;

class PredictionOpenedNotification extends Notification
{
    use Queueable;

    public function __construct(protected PredictionMatch $match) {}

    public function via(mixed $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(mixed $notifiable): array
    {
        return [
            'type'         => 'prediction_opened',
            'match_id'     => $this->match->id,
            'title'        => $this->buildTitle(),
            'body'         => 'توقّع النتيجة واربح ' . $this->match->reward_points . ' نقطة!',
            'team1'        => $this->match->team1_name,
            'team2'        => $this->match->team2_name,
            'reward'       => $this->match->reward_points,
            'close_time'   => optional($this->match->prediction_close_time)->toIso8601String(),
            'image'        => $this->match->team1_logo,
            'link'         => '/',
        ];
    }

    private function buildTitle(): string
    {
        return '⚽ ' . $this->match->team1_name . ' vs ' . $this->match->team2_name;
    }
}
