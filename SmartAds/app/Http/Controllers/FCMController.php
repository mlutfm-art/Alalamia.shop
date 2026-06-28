<?php
namespace Modules\SmartAds\app\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Models\User;
use Modules\SmartAds\app\Models\DeviceToken;
use Modules\SmartAds\app\Services\FCMService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class FCMController extends Controller
{
    public function __construct(protected FCMService $fcm) {}
    public function saveToken(Request $request): JsonResponse
    {
        $request->validate(['token' => 'required|string|max:512', 'user_id' => 'nullable|integer|exists:users,id', 'device_type' => 'nullable|string|max:100', 'browser' => 'nullable|string|max:100']);
        $user = Auth::guard('api')->user() ?? Auth::guard('customer')->user() ?? null;
        DeviceToken::saveToken($request->token, $user?->id ?? $request->input('user_id'), $user ? get_class($user) : ($request->input('user_id') ? User::class : null), $request->input('browser', 'unknown'));
        $userId = $user?->id ?? $request->input('user_id');
        if ($userId) { User::where('id', $userId)->update(['cm_firebase_token' => $request->token]); }
        return response()->json(['success' => true, 'message' => 'Token saved']);
    }
    public function deleteToken(Request $request): JsonResponse
    {
        $request->validate(['token' => 'required|string']);
        DeviceToken::where('token', $request->token)->delete();
        return response()->json(['success' => true]);
    }
    public function send(Request $request): JsonResponse
    {
        $request->validate(['token' => 'nullable|string|max:512', 'title' => 'required|string|max:255', 'body' => 'required|string|max:1000', 'data' => 'nullable|array']);
        $result = $request->filled('token') ? $this->fcm->sendToToken($request->token, $request->title, $request->body, $request->input('data', [])) : $this->fcm->sendToAll($request->title, $request->body, $request->input('data', []));
        return response()->json(['success' => true, 'result' => $result]);
    }
    public function sendFromAdmin(Request $request): JsonResponse
    {
        $request->validate(['token' => 'nullable|string|max:512', 'title' => 'required|string|max:255', 'body' => 'required|string|max:1000']);
        $result = $request->filled('token') ? $this->fcm->sendToToken($request->token, $request->title, $request->body) : $this->fcm->sendToAll($request->title, $request->body);
        return response()->json(['success' => true, 'result' => $result]);
    }
    public function adminSendPage() { return view('smartads::admin.fcm-send', ['totalTokens' => DeviceToken::count()]); }
    public function testPage() { return view('smartads::fcm.test', ['config' => config('smartads-fcm'), 'totalTokens' => DeviceToken::count()]); }
}
