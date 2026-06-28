<?php
namespace Modules\Predictions\app\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Modules\Predictions\app\Events\PredictionMatchOpened;
use Modules\Predictions\app\Events\BannerActivated;
use Modules\Predictions\app\Models\PredictionMatch;
use Modules\Predictions\app\Models\MatchPrediction;
use Modules\Predictions\app\Models\PredictionSetting;
use Modules\Predictions\app\Services\PredictionService;

class PredictionController extends Controller
{
    public function __construct(protected PredictionService $service) {}

    public function index(): View
    {
        $stats   = $this->service->getDashboardStats();
        $chart   = $this->service->getMonthlyChartData();
        $matches = PredictionMatch::withCount('predictions')->orderByDesc('id')->limit(5)->get();
        return view('predictions::admin.index', compact('stats', 'chart', 'matches'));
    }

    public function matchesList(Request $request): View
    {
        $search  = $request->get('search');
        $status  = $request->get('status');
        $matches = PredictionMatch::withCount('predictions')
            ->when($search, fn($q) => $q->where(fn($q2) => $q2
                ->where('team1_name', 'like', "%$search%")
                ->orWhere('team2_name', 'like', "%$search%")
                ->orWhere('title',     'like', "%$search%")))
            ->when($status, fn($q) => $q->where('status', $status))
            ->orderByDesc('id')->paginate(15)->withQueryString();
        return view('predictions::admin.matches', compact('matches', 'search', 'status'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'                => 'nullable|string|max:255',
            'team1_name'           => 'required|string|max:255',
            'team2_name'           => 'required|string|max:255',
            'team1_logo'           => 'nullable|url|max:1000',
            'team2_logo'           => 'nullable|url|max:1000',
            'match_time'           => 'required|date',
            'prediction_close_time'=> 'required|date|before:match_time',
            'reward_points'        => 'required|integer|min:1|max:100000',
            'notes'                => 'nullable|string|max:1000',
        ]);

        $match = PredictionMatch::create($data + ['status' => 'active']);

        /* ── Fire notification event ── */
        event(new PredictionMatchOpened($match));

        return redirect()->route('admin.predictions.matches')
            ->with('success', translate('Match_created_successfully') . ' — ' . translate('Notification_sent'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $match = PredictionMatch::findOrFail($id);
        $wasInactive = $match->status !== 'active';

        $data = $request->validate([
            'title'                => 'nullable|string|max:255',
            'team1_name'           => 'required|string|max:255',
            'team2_name'           => 'required|string|max:255',
            'team1_logo'           => 'nullable|url|max:1000',
            'team2_logo'           => 'nullable|url|max:1000',
            'match_time'           => 'required|date',
            'prediction_close_time'=> 'required|date|before:match_time',
            'reward_points'        => 'required|integer|min:1|max:100000',
            'notes'                => 'nullable|string|max:1000',
        ]);

        $match->update($data);

        /* ── Fire notification if match newly became active ── */
        if ($wasInactive && ($data['status'] ?? $match->status) === 'active') {
            event(new PredictionMatchOpened($match->fresh()));
        }

        return redirect()->route('admin.predictions.matches')
            ->with('success', translate('Match_updated_successfully'));
    }

    public function destroy(int $id): RedirectResponse
    {
        $match = PredictionMatch::findOrFail($id);
        $match->predictions()->delete();
        $match->delete();
        return redirect()->route('admin.predictions.matches')
            ->with('success', translate('Match_deleted_successfully'));
    }

    /** Admin can manually resend notification for any active match. */
    public function notify(int $id): RedirectResponse
    {
        $match = PredictionMatch::findOrFail($id);
        event(new PredictionMatchOpened($match));
        return redirect()->back()
            ->with('success', translate('Notification_sent_to_all_customers'));
    }

    public function submitResult(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'actual_team1' => 'required|integer|min:0|max:99',
            'actual_team2' => 'required|integer|min:0|max:99',
        ]);
        try {
            $result = $this->service->evaluateMatchPredictions(
                $id,
                (int)$request->actual_team1,
                (int)$request->actual_team2
            );
            return redirect()->route('admin.predictions.matches')
                ->with('success',
                    translate('Match_result_submitted') . ": {$result['evaluated']} " .
                    translate('evaluated') . ", {$result['exact']} " .
                    translate('exact') . ", {$result['partial']} " .
                    translate('partial')
                );
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function predictionsList(Request $request): View
    {
        $search      = $request->get('search');
        $status      = $request->get('status');
        $predictions = MatchPrediction::with(['match_details', 'user'])
            ->when($search, fn($q) => $q->whereHas('user', fn($q2) => $q2
                ->where('f_name', 'like', "%$search%")
                ->orWhere('l_name', 'like', "%$search%")
                ->orWhere('email',  'like', "%$search%")))
            ->when($status, fn($q) => $q->where('prediction_status', $status))
            ->orderByDesc('id')->paginate(20)->withQueryString();
        return view('predictions::admin.predictions-list', compact('predictions', 'search', 'status'));
    }

    public function leaderboard(Request $request): View
    {
        $period = $request->get('period', 'all');
        $users  = $this->service->getLeaderboard($period);
        return view('predictions::admin.leaderboard', compact('users', 'period'));
    }

    public function settings(): View
    {
        $settings = [
            'enabled'                   => PredictionSetting::get('enabled',                   '1'),
            'default_reward_points'     => PredictionSetting::get('default_reward_points',     '100'),
            'close_threshold'           => PredictionSetting::get('close_threshold',           '2'),
            'partial_reward_multiplier' => PredictionSetting::get('partial_reward_multiplier', '0.5'),
            // Banner settings
            'banner_enabled'            => PredictionSetting::get('banner_enabled',     '0'),
            'banner_title'              => PredictionSetting::get('banner_title',       ''),
            'banner_description'        => PredictionSetting::get('banner_description', ''),
            'banner_image'              => PredictionSetting::get('banner_image',       ''),
            'banner_button_text'        => PredictionSetting::get('banner_button_text', ''),
            'banner_match_id'           => PredictionSetting::get('banner_match_id',    ''),
        ];
        $activeMatches = \Modules\Predictions\app\Models\PredictionMatch::where('status','active')->orderByDesc('id')->get(['id','title','team1_name','team2_name']);
        return view('predictions::admin.settings', compact('settings', 'activeMatches'));
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'enabled'                   => 'required|in:0,1',
            'default_reward_points'     => 'required|integer|min:1|max:100000',
            'close_threshold'           => 'required|integer|min:0|max:20',
            'partial_reward_multiplier' => 'required|numeric|min:0|max:1',
            // Banner validation
            'banner_enabled'            => 'nullable|in:0,1',
            'banner_title'              => 'nullable|string|max:255',
            'banner_description'        => 'nullable|string|max:500',
            'banner_image'              => 'nullable|url|max:1000',
            'banner_button_text'        => 'nullable|string|max:100',
            'banner_match_id'           => 'nullable|integer|exists:prediction_matches,id',
        ]);

        $wasEnabled = PredictionSetting::get('banner_enabled', '0') === '1';

        // Normalize banner_enabled (checkbox sends nothing when unchecked)
        $data['banner_enabled'] = $request->has('banner_enabled') ? '1' : '0';

        foreach ($data as $k => $v) {
            PredictionSetting::set($k, (string)($v ?? ''));
        }

        // Fire banner notification when banner is activated or updated while active
        $nowEnabled = $data['banner_enabled'] === '1';
        if ($nowEnabled) {
            $triggerType = (!$wasEnabled) ? 'activated' : 'updated';
            event(new BannerActivated(
                title:       $data['banner_title']       ?? '',
                description: $data['banner_description'] ?? '',
                image:       $data['banner_image']       ?? '',
                buttonText:  $data['banner_button_text'] ?? '',
                matchId:     !empty($data['banner_match_id']) ? (int)$data['banner_match_id'] : null,
                triggerType: $triggerType,
            ));
        }
        return redirect()->route('admin.predictions.settings')
            ->with('success', translate('Settings_saved_successfully'));
    }
}
