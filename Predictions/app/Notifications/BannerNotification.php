<?php
namespace Modules\Predictions\app\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;

class BannerNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected string  $title,
        protected string  $body,
        protected string  $image,
        protected ?int    $matchId,
        protected string  $type = 'banner_activated',
    ) {}

    public function via(mixed $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(mixed $notifiable): array
    {
        return [
            'type'     => $this->type,
            'title'    => $this->title,
            'body'     => $this->body,
            'image'    => $this->image,
            'match_id' => $this->matchId,
            'link'     => $this->matchId ? '/predictions/match/' . $this->matchId : '/',
        ];
    }
}
