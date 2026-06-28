@extends('layouts.admin.app')
@section('title', 'لوحة تحكم البوت الذكي')

@section('content')
<div class="content container-fluid">
    <div class="page-header mb-4">
        <h1 class="page-header-title"><i class="tio-robot mr-2 text-primary"></i>لوحة تحكم البوت الذكي</h1>
        <a href="{{ route('admin.smartads.index') }}" class="btn btn-outline-secondary btn-sm">العودة لـ SmartAds</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="card text-center"><div class="card-body py-3"><div class="h3 text-primary">15+</div><small>قاعدة ذكية</small></div></div></div>
        <div class="col-md-3"><div class="card text-center"><div class="card-body py-3"><div class="h3 text-success">{{ $productsCount }}</div><small>منتج في المتجر</small></div></div></div>
        <div class="col-md-3"><div class="card text-center"><div class="card-body py-3"><div class="h3 text-info">{{ $usersCount }}</div><small>مستخدم مسجل</small></div></div></div>
        <div class="col-md-3"><div class="card text-center"><div class="card-body py-3"><div class="h3 text-warning">✅</div><small>نشط</small></div></div></div>
    </div>

    <div class="card mb-4">
        <div class="card-header"><h5>🧪 اختبار البوت</h5></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.smartads.chatbot.dashboard') }}">
                @csrf
                <div class="input-group">
                    <input type="text" name="test_message" class="form-control" placeholder="اكتب سؤالاً للبوت..." value="{{ $testResult['message'] ?? '' }}">
                    <button class="btn btn-primary" type="submit">إرسال</button>
                </div>
            </form>
            @if($testResult)
            <div class="mt-3 p-3 bg-light rounded">
                <div class="fw-bold text-primary">👤 أنت: {{ $testResult['message'] }}</div>
                <div class="mt-2">🤖 البوت: {!! nl2br(e($testResult['reply'])) !!}</div>
            </div>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h5>📋 روابط سريعة</h5></div>
        <div class="card-body d-flex gap-2 flex-wrap">
            <a href="{{ url('/public/chatbot.html') }}" target="_blank" class="btn btn-outline-info btn-sm">صفحة الديمو</a>
            <a href="{{ url('/public/chatbot-api.php') }}" target="_blank" class="btn btn-outline-success btn-sm">حالة API</a>
        </div>
    </div>
</div>
@endsection
