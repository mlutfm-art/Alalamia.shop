@extends('layouts.admin.app')
@section('title', 'SmartAds - لوحة التحكم')

@push('css_or_js')
<style>
    .bot-panel { border-left: 4px solid #6f42c1; }
    .bot-panel .card-header { background: #f8f9fa; font-weight: bold; }
</style>
@endpush

@section('content')
<div class="content container-fluid">
    <div class="page-header mb-4">
        <h1 class="page-header-title"><i class="tio-chart-bar-3 mr-2 text-primary"></i>SmartAds</h1>
    </div>

    {{-- أزرار الخدمات --}}
    <div class="row g-2 mb-4">
        <div class="col-6 col-md-3 col-lg-2"><a href="{{ url('/admin/smartads/groups') }}" class="btn btn-outline-primary btn-sm w-100"><i class="tio-group mr-1"></i> مجموعات</a></div>
        <div class="col-6 col-md-3 col-lg-2"><a href="{{ route('admin.smartads.dose-reminders') }}" class="btn btn-outline-success btn-sm w-100"><i class="tio-medicine-outlined mr-1"></i> تذكير الجرعات</a></div>
        <div class="col-6 col-md-3 col-lg-2"><a href="{{ url('/admin/smartads/occasions') }}" class="btn btn-outline-danger btn-sm w-100"><i class="tio-calendar-event mr-1"></i> المناسبات</a></div>
        <div class="col-6 col-md-3 col-lg-2"><a href="{{ url('/admin/smartads/segment-send') }}" class="btn btn-outline-info btn-sm w-100"><i class="tio-send-outlined mr-1"></i> إرسال حسب الفئة</a></div>
        <div class="col-6 col-md-3 col-lg-2"><a href="{{ url('/admin/smartads/notification-templates') }}" class="btn btn-outline-secondary btn-sm w-100"><i class="tio-file-text-outlined mr-1"></i> قوالب</a></div>
        <div class="col-6 col-md-3 col-lg-2"><a href="{{ route('admin.smartads.create') }}" class="btn btn-primary btn-sm w-100"><i class="tio-add mr-1"></i> إعلان جديد</a></div>
    </div>

    {{-- إحصائيات --}}
    <div class="row g-3 mb-4">
        <div class="col-4 col-md-2"><div class="card text-center"><div class="card-body py-3"><div class="text-muted small">الإعلانات</div><div class="h3 text-primary mb-0">{{ $stats['total'] ?? 0 }}</div></div></div></div>
        <div class="col-4 col-md-2"><div class="card text-center"><div class="card-body py-3"><div class="text-muted small">النشطة</div><div class="h3 text-success mb-0">{{ $stats['active'] ?? 0 }}</div></div></div></div>
        <div class="col-4 col-md-2"><div class="card text-center"><div class="card-body py-3"><div class="text-muted small">الظهور</div><div class="h3 text-info mb-0">{{ number_format($stats['impressions'] ?? 0) }}</div></div></div></div>
        <div class="col-4 col-md-2"><div class="card text-center"><div class="card-body py-3"><div class="text-muted small">الضغطات</div><div class="h3 text-warning mb-0">{{ number_format($stats['clicks'] ?? 0) }}</div></div></div></div>
    </div>

    {{-- جدول الإعلانات --}}
    <div class="card mb-4">
        <div class="card-header">قائمة الإعلانات</div>
        <div class="table-responsive"><table class="table table-hover mb-0">
            <thead><tr><th>#</th><th>الإعلان</th><th>النوع</th><th>ظهور</th><th>ضغط</th><th>CTR%</th><th>الحالة</th><th>إجراءات</th></tr></thead>
            <tbody>
                @forelse($ads as $ad)
                <tr><td>{{ $ad->id }}</td><td>{{ $ad->title }}</td><td>{{ $ad->ad_type }}</td><td>{{ $ad->impressions }}</td><td>{{ $ad->clicks }}</td><td>{{ $ad->impressions > 0 ? round(($ad->clicks/$ad->impressions)*100,1) : 0 }}%</td><td><span class="badge badge-{{ $ad->status ? 'success' : 'secondary' }}">{{ $ad->status ? 'نشط' : 'غير نشط' }}</span></td><td><a href="{{ route('admin.smartads.edit', $ad->id) }}" class="btn btn-sm btn-outline-primary">✏️</a></td></tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted">لا توجد إعلانات</td></tr>
                @endforelse
            </tbody>
        </table></div>
    </div>

    {{-- 🤖 لوحة تحكم البوت (مدمجة) --}}
    <div class="card bot-panel" id="botPanel">
        <div class="card-header">
            <i class="tio-robot mr-2"></i> 🤖 لوحة تحكم البوت الذكي
            <a href="{{ url('/public/chatbot-demo.html') }}" target="_blank" class="btn btn-sm btn-outline-info float-left">فتح الديمو</a>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <h5>حالة البوت</h5>
                    <p>✅ البوت نشط بقواعد ذكية (15+ قاعدة).</p>
                    <p>📡 API: <code>/public/chatbot-api.php</code></p>
                    <p>📱 صفحة الديمو: <code>/public/chatbot-demo.html</code></p>
                </div>
                <div class="col-md-6">
                    <h5>اختبار سريع</h5>
                    <input type="text" id="testMsg" class="form-control mb-2" placeholder="اكتب سؤالاً للبوت...">
                    <button onclick="testBot()" class="btn btn-primary btn-sm">إرسال</button>
                    <div id="testResponse" class="mt-2 small"></div>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
async function testBot() {
    const msg = document.getElementById('testMsg').value;
    if (!msg) return;
    const res = await fetch('https://alalamia.shop/public/chatbot-api.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
        body: JSON.stringify({message: msg})
    });
    const data = await res.json();
    document.getElementById('testResponse').innerHTML = '<strong>🤖 البوت:</strong> ' + (data.reply || JSON.stringify(data));
}
</script>
<!-- 🤖 لوحة تحكم البوت -->
<div class="card mt-4" id="chatbotPanel">
    <div class="card-header bg-purple text-white">
        <h5 class="mb-0"><i class="tio-robot mr-2"></i>🤖 اختبار البوت الذكي</h5>
    </div>
    <div class="card-body">
        <div class="input-group">
            <input type="text" id="testMsg" class="form-control" placeholder="اكتب سؤالاً للبوت...">
            <button class="btn btn-primary" onclick="testChatbot()">إرسال</button>
        </div>
        <div id="testResponse" class="mt-3" style="display:none; padding:15px; background:#f8f9fa; border-radius:8px;"></div>
    </div>
</div>

<script>
async function testChatbot() {
    const msg = document.getElementById("testMsg").value.trim();
    if (!msg) return;
    const btn = document.querySelector("#chatbotPanel button");
    btn.disabled = true;
    btn.textContent = "⏳";
    try {
        const res = await fetch("https://alalamia.shop/public/chatbot-api.php", {
            method: "POST",
            headers: {"Content-Type": "application/json"},
            body: JSON.stringify({message: msg})
        });
        const data = await res.json();
        const respDiv = document.getElementById("testResponse");
        respDiv.style.display = "block";
        respDiv.innerHTML = "<strong>👤 أنت:</strong> " + msg + "<br><strong>🤖 البوت:</strong> " + (data.reply || JSON.stringify(data));
    } catch(e) {
        document.getElementById("testResponse").style.display = "block";
        document.getElementById("testResponse").innerHTML = "❌ حدث خطأ: " + e.message;
    }
    btn.disabled = false;
    btn.textContent = "إرسال";
}
</script>
<!-- نهاية لوحة البوت -->
@endsection
