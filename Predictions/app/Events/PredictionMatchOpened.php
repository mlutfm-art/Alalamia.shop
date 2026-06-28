<?php
namespace Modules\Predictions\app\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Predictions\app\Models\PredictionMatch;

class PredictionMatchOpened
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly PredictionMatch $match) {}
}
