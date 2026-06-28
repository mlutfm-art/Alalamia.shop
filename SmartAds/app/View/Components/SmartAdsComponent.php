<?php

namespace Modules\SmartAds\app\View\Components;

use Illuminate\View\Component;
use Modules\SmartAds\app\Models\SmartAd;
use Modules\SmartAds\app\Services\ActionResolverService;
use Modules\SmartAds\app\Services\FlutterResponseBuilder;

/**
 * استخدام في أي Blade:
 *   <x-smartads placement="home" />
 *   <x-smartads placement="category" device="android" :limit="3" />
 *   <x-smartads placement="product" type="popup" :category-id="$cat->id" />
 */
class SmartAdsComponent extends Component
{
    /** الإعلانات المُحضَّرة + payload لكل منها */
    public array $ads = [];

    public function __construct(
        private readonly ActionResolverService  $resolver,
        private readonly FlutterResponseBuilder $builder,
        public string  $placement  = 'home',
        public ?string $device     = null,
        public ?string $type       = null,   // banner | popup | notification | video | native
        public ?int    $categoryId = null,
        public ?string $region     = null,
        public int     $limit      = 5,
    ) {
        $this->ads = $this->loadAds();
    }

    private function loadAds(): array
    {
        $query = SmartAd::active()
            ->forPlacement($this->placement)
            ->forDevice($this->device)
            ->forCategory($this->categoryId)
            ->forRegion($this->region)
            ->whereNull('parent_id');

        if ($this->type) {
            $query->where('ad_type', $this->type);
        }

        return $query->latest()
            ->take($this->limit)
            ->get()
            ->map(function (SmartAd $ad) {
                $resolved = $this->resolver->resolve($ad->action_data);
                return [
                    'model'   => $ad,
                    'payload' => $this->builder->build($ad, $resolved, $ad->id),
                ];
            })
            ->all();
    }

    public function render()
    {
        return view('smartads::components.smartads');
    }
}
