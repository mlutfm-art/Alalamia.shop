<?php
namespace Modules\Predictions\app\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Predictions\app\Models\PredictionMatch;
use Modules\Predictions\app\Models\MatchPrediction;
use Modules\Predictions\app\Models\PredictionSetting;
use Modules\Predictions\app\Services\PredictionService;

class PredictionApiController extends Controller {
    public function __construct(protected PredictionService $service) {}

    private function resolveUserId(Request $request): ?int {
        $id = auth('api')->id() ?: auth('customer')->id();
        if ($id) return (int)$id;
        $h = $request->header('X-User-Id') ?: $request->header('user-id');
        return $h ? (int)$h : null;
    }

    public function activeMatches(Request $request): JsonResponse {
        $userId = $this->resolveUserId($request);
        $matches = PredictionMatch::where('status','active')
            ->where('prediction_close_time','>',now())
            ->when($userId, function($q) use ($userId) {
                $q->whereDoesntHave('predictions', function($sq) use ($userId) {
                    $sq->where('user_id', $userId);
                });
            })
            ->orderBy('prediction_close_time')
            ->get();
            
        return response()->json($matches->map(function($match) {
            return [
                'id'                    => $match->id,
                'title'                 => $match->title,
                'team1_name'            => $match->team1_name,
                'team2_name'            => $match->team2_name,
                'team1_logo'            => $match->team1_logo,
                'team2_logo'            => $match->team2_logo,
                'reward_points'         => (int)$match->reward_points,
                'prediction_close_time' => $match->prediction_close_time->toIso8601String(),
                'is_expired'            => false,
            ];
        }));
    }

    public function submit(Request $request): JsonResponse {
        $userId = $this->resolveUserId($request);
        if (!$userId) return response()->json(['success' => false, 'error' => 'auth_required'], 401);

        $request->validate([
            'match_id'        => 'required|exists:prediction_matches,id',
            'predicted_team1' => 'required|integer|min:0|max:99',
            'predicted_team2' => 'required|integer|min:0|max:99',
        ]);

        $match = PredictionMatch::findOrFail($request->match_id);
        if (!$match->isOpen()) return response()->json(['success'=>false,'error'=>'prediction_closed'], 400);
        
        if (MatchPrediction::where('match_id',$match->id)->where('user_id',$userId)->exists()) {
            return response()->json(['success'=>false,'error'=>'already_predicted'], 400);
        }

        $prediction = MatchPrediction::create([
            'match_id'          => $match->id,
            'user_id'           => $userId,
            'predicted_team1'   => (int)$request->predicted_team1,
            'predicted_team2'   => (int)$request->predicted_team2,
            'prediction_status' => 'pending',
        ]);

        return response()->json(['success' => true, 'prediction' => $prediction], 201);
    }

    public function myPredictions(Request $request): JsonResponse {
        $userId = $this->resolveUserId($request);
        if (!$userId) return response()->json(['success'=>false,'error'=>'auth_required'], 401);
        return response()->json(MatchPrediction::with('match_details')->where('user_id',$userId)->orderByDesc('id')->get());
    }

    public function leaderboard(Request $request): JsonResponse {
        return response()->json($this->service->getLeaderboard($request->get('period','all'), 50));
    }

    public function activeBanner(): JsonResponse {
        if (PredictionSetting::get('banner_enabled', '0') !== '1') return response()->json(['show_banner' => false]);
        $match = PredictionMatch::where('status', 'active')->where('prediction_close_time', '>', now())->orderBy('prediction_close_time')->first();
        return response()->json([
            'show_banner' => true,
            'title' => PredictionSetting::get('banner_title', 'توقع واربح! 🏆'),
            'description' => PredictionSetting::get('banner_description', 'شارك بتوقعاتك واربح نقاط ولاء فورية'),
            'image' => PredictionSetting::get('banner_image', ''),
            'match_id' => $match?->id,
            'team1' => $match?->team1_name,
            'team2' => $match?->team2_name,
            'action' => $match ? 'open_prediction' : 'none',
        ]);
    }
}
