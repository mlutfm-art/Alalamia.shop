<?php
namespace Modules\Predictions\app\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BannerActivated
{
    use Dispatchable, SerializesModels;

    /**
     * @param string $title       Banner title
     * @param string $description Banner description
     * @param string $image       Banner image URL
     * @param string $buttonText  CTA button text
     * @param int|null $matchId   Target match ID (nullable)
     * @param string $triggerType 'activated' | 'updated' | 'published'
     */
    public function __construct(
        public readonly string  $title,
        public readonly string  $description,
        public readonly string  $image,
        public readonly string  $buttonText,
        public readonly ?int    $matchId,
        public readonly string  $triggerType = 'activated',
    ) {}
}
