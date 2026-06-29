<?php

namespace Modules\SmartAds\app\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    public function index()
    {
        return response()->json(['message' => 'Chatbot Dashboard fix active']);
    }
}
