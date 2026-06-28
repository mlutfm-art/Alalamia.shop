<?php
namespace Modules\SmartAds\Helpers;

use Modules\SmartAds\app\Models\SmartAd;

class SmartAdsHelper
{
    public static function get($placement)
    {
        return SmartAd::active()->forPlacement($placement)->get();
    }

    public static function getForDevice($placement, $device = null)
    {
        return SmartAd::active()->forPlacement($placement)->forDevice($device)->whereNull('parent_id')->get();
    }
}
