<?php
namespace Modules\Predictions\app\Services;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Predictions\app\Models\PredictionMatch;
use Modules\Predictions\app\Models\MatchPrediction;

class PredictionService {
    public function evaluateMatchPredictions(int $matchId, int $actualTeam1, int $actualTeam2): array {
        $match = PredictionMatch::findOrFail($matchId);
        if ($match->status === 'completed') throw new \RuntimeException('هذه المباراة تم تقييمها مسبقاً.');
        
        $rewardPoints   = $match->reward_points;
        $closeThreshold = (int) config('predictions.close_threshold', 2);
        $partialMulti   = (float) config('predictions.partial_reward_multiplier', 0.5);
        $evaluated = $exact = $partial = 0;

        $winners = [];

        DB::transaction(function() use ($match, $matchId, $actualTeam1, $actualTeam2, $rewardPoints, $closeThreshold, $partialMulti, &$evaluated, &$exact, &$partial, &$winners) {
            $predictions = MatchPrediction::where('match_id', $matchId)->where('prediction_status', 'pending')->get();
            
            foreach ($predictions as $p) {
                $distance = abs($actualTeam1 - $p->predicted_team1) + abs($actualTeam2 - $p->predicted_team2);
                
                if ($distance === 0) { 
                    $points = $rewardPoints; 
                    $exact++; 
                } elseif ($distance <= $closeThreshold) { 
                    $points = (int)round($rewardPoints * $partialMulti); 
                    $partial++; 
                } else { 
                    $points = 0; 
                }

                $p->update([
                    'points_awarded'    => $points,
                    'distance_score'    => $distance,
                    'prediction_status' => 'evaluated'
                ]);

                // إذا فاز بنقاط، نضيفه لقائمة الإشعارات
                if ($points > 0) {
                    $winners[] = ['user_id' => $p->user_id, 'points' => $points];
                }

                $evaluated++;
            }
            
            $match->update([
                'actual_team1' => $actualTeam1,
                'actual_team2' => $actualTeam2,
                'status'       => 'completed'
            ]);
        });

        // إرسال إشعارات فردية للفائزين بعد نجاح المعاملة
        $this->notifyWinners($winners, $match);

        return ['evaluated' => $evaluated, 'exact' => $exact, 'partial' => $partial];
    }

    private function notifyWinners(array $winners, $match): void {
        foreach ($winners as $winner) {
            try {
                $user = \App\Models\User::find($winner['user_id']);
                if ($user && $user->cm_firebase_token) {
                    $title = '🎉 مبروك! لقد فزت بتوقعك';
                    $body  = "توقعك لمباراة {$match->team1_name} ضد {$match->team2_name} كان قريباً، وحصلت على {$winner['points']} نقطة!";
                    
                    if (class_exists('\App\CentralLogics\Helpers')) {
                        \App\CentralLogics\Helpers::send_push_notif_to_device($user->cm_firebase_token, [
                            'title'       => $title,
                            'description' => $body,
                            'image'       => '',
                            'type'        => 'prediction',
                            'match_id'    => (string)$match->id,
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                \Log::error("[Predictions] Winner Notification Failed: " . $e->getMessage());
            }
        }
    }

    public function getLeaderboard(string $period='all', int $limit=50): Collection {
        $q = DB::table('match_predictions as mp')
            ->join('users as u','u.id','=','mp.user_id')
            ->where('mp.prediction_status','evaluated')
            ->select('u.id','u.f_name','u.l_name','u.email',
                DB::raw('COUNT(mp.id) as total_predictions'),
                DB::raw('SUM(mp.points_awarded) as total_points'),
                DB::raw('SUM(CASE WHEN mp.distance_score=0 THEN 1 ELSE 0 END) as correct_full_count'));
        $q = $this->applyPeriodFilter($q,$period);
        return $q->groupBy('u.id','u.f_name','u.l_name','u.email')
            ->orderByDesc('total_points')->orderByDesc('correct_full_count')
            ->limit($limit)->get()
            ->map(function($row,$i){ $row->rank=$i+1; return $row; });
    }

    public function getDashboardStats(): array {
        return [
            'total_matches'         => PredictionMatch::count(),
            'active_matches'        => PredictionMatch::where('status','active')->count(),
            'completed_matches'     => PredictionMatch::where('status','completed')->count(),
            'total_predictions'     => MatchPrediction::count(),
            'evaluated_predictions' => MatchPrediction::where('prediction_status','evaluated')->count(),
            'unique_participants'   => MatchPrediction::distinct('user_id')->count('user_id'),
        ];
    }

    public function getMonthlyChartData(): array {
        $rows = DB::table('match_predictions')
            ->selectRaw("DATE_FORMAT(created_at,'%Y-%m') as month, COUNT(*) as total")
            ->where('created_at','>=',now()->subMonths(6))
            ->groupByRaw("DATE_FORMAT(created_at,'%Y-%m')")->orderBy('month')->get();
        return ['labels'=>$rows->pluck('month')->toArray(),'data'=>$rows->pluck('total')->map(fn($v)=>(int)$v)->toArray()];
    }

    private function applyPeriodFilter($query, string $period) {
        return match($period) {
            'daily'   => $query->whereDate('mp.created_at',today()),
            'weekly'  => $query->where('mp.created_at','>=',now()->startOfWeek()),
            'monthly' => $query->where('mp.created_at','>=',now()->startOfMonth()),
            default   => $query,
        };
    }
}
