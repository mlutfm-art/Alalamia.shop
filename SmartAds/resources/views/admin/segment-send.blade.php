@extends('layouts.admin.app')
@section('title', 'إرسال حسب الفئة - متجر العالمية')

@push('css_or_js')
<style>
.segment-card { cursor: pointer; transition: .2s; border: 2px solid transparent; border-radius: 10px; }
.segment-card:hover { border-color: #2196F3; }
.segment-card.active { border-color: #2196F3; background: #e3f2fd; }
</style>
@endpush

@section('content')
<div class="content container-fluid">
    <div class="page-header mb-4">
            <a href="{{ route('admin.smartads.index') }}" class="btn btn-outline-secondary btn-sm"><i class="tio-arrow-backward mr-1"></i> العودة لـ SmartAds</a>
        <h1 class="page-header-title"><i class="tio-send-outlined mr-2 text-primary"></i>إرسال حسب الفئة</h1>
        <small class="text-muted">اختر شريحة العملاء، حدد الإعدادات، ثم أرسل الإشعار</small>
    </div>

    <div class="row g-2 mb-3">
        @foreach($segments as $key => $s)
        <div class="col-6 col-md-3 col-lg-2">
            <div class="card segment-card p-2 text-center" data-segment="{{ $key }}" onclick="selectSegment('{{ $key }}')">
                <div style="font-size:24px">{{ $s[0] }}</div>
                <div class="fw-semibold small">{{ $s[1] }}</div>
            </div>
        </div>
        @endforeach
    </div>

    <div id="filterCard" style="display:none">
        <div class="card mb-3">
            <div class="card-header">⚙️ إعدادات: <span id="segTitle" class="text-primary"></span></div>
            <div class="card-body">
                <input type="hidden" id="currentSegment">
                <div class="row g-2" id="filterFields"></div>
                <button class="btn btn-info btn-sm mt-2" onclick="preview()">👥 معاينة العدد</button>
                <span id="previewCount" class="ms-2 small"></span>
            </div>
        </div>

        <div class="card">
            <div class="card-header">📨 إرسال الإشعار</div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-6"><label class="fw-semibold">العنوان</label><input type="text" id="title" class="form-control"></div>
                    <div class="col-md-6"><label class="fw-semibold">النص</label><textarea id="body" class="form-control" rows="2"></textarea></div>
                </div>
                <button class="btn btn-primary mt-2" onclick="sendNow()">📨 إرسال</button>
                <span id="result" class="ms-2"></span>
            </div>
        </div>
    </div>
</div>

<script>
const CSRF = '{{ csrf_token() }}';
let currentSegment = '';

function selectSegment(key) {
    currentSegment = key;
    document.querySelectorAll('.segment-card').forEach(c => c.classList.remove('active'));
    document.querySelector('[data-segment="'+key+'"]').classList.add('active');
    document.getElementById('filterCard').style.display = '';
    document.getElementById('segTitle').textContent = document.querySelector('[data-segment="'+key+'"] .fw-semibold').textContent;
    document.getElementById('currentSegment').value = key;
    buildFilters(key);
}

function buildFilters(key) {
    let h = '';
    if (key === 'product_buyers') h += '<div class="col-md-6"><label>المنتج</label><select class="form-control" id="product_id" onchange="document.getElementById(\'product_id\').value=this.value">@foreach(\App\Models\Product::active()->get() as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach</select></div>';
    if (key === 'price_range') h += '<div class="col-3"><label>الحد الأدنى</label><input type="number" id="min_price" class="form-control" value="0"></div><div class="col-3"><label>الحد الأعلى</label><input type="number" id="max_price" class="form-control" value="999999"></div>';
    if (key === 'order_status') h += '<div class="col-4"><label>حالة الطلب</label><select id="order_status" class="form-control"><option value="pending">قيد الانتظار</option><option value="processing">تجهيز</option><option value="confirmed">مؤكد</option><option value="delivered">تم التسليم</option></select></div>';
    if (key === 'last_order_days') h += '<div class="col-3"><label>أيام منذ آخر طلب</label><input type="number" id="days" class="form-control" value="30"></div>';
    if (key === 'city') h += '<div class="col-4"><label>المدينة</label><input type="text" id="city" class="form-control" placeholder="صنعاء"></div>';
    if (key === 'category_buyers') h += '<div class="col-4"><label>القسم</label><select id="category_id" class="form-control">@foreach($categories as $cat)<option value="{{ $cat->id }}">{{ $cat->name }}</option>@endforeach</select></div>';
    if (key === 'order_count') h += '<div class="col-3"><label>عدد الطلبات (أكثر من)</label><input type="number" id="order_count" class="form-control" value="5"></div>';
    if (key === 'payment_status') h += '<div class="col-4"><label>حالة الدفع</label><select id="payment_status" class="form-control"><option value="unpaid">لم يدفع</option><option value="paid">تم الدفع</option></select></div>';
    if (key === 'registered_days') h += '<div class="col-3"><label>أيام منذ التسجيل</label><input type="number" id="reg_days" class="form-control" value="30"></div>';
    document.getElementById('filterFields').innerHTML = h || '<div class="col-12 text-muted">لا توجد إعدادات إضافية</div>';
}

function getData() {
    return {
        type: currentSegment,
        product_id: document.getElementById('product_id')?.value,
        min_price: document.getElementById('min_price')?.value,
        max_price: document.getElementById('max_price')?.value,
        order_status: document.getElementById('order_status')?.value,
        days: document.getElementById('days')?.value || document.getElementById('reg_days')?.value,
        city: document.getElementById('city')?.value,
        category_id: document.getElementById('category_id')?.value,
        order_count: document.getElementById('order_count')?.value,
        payment_status: document.getElementById('payment_status')?.value,
    };
}

function preview() {
    fetch('/admin/smartads/segment-preview', {
        method: 'POST', headers: {'Content-Type':'application/json', 'X-CSRF-TOKEN': CSRF},
        body: JSON.stringify(getData())
    }).then(r => r.json()).then(d => {
        document.getElementById('previewCount').innerHTML = '👥 ' + d.total + ' مستخدم | 📱 ' + d.fcm_count + ' جوال';
    });
}

function sendNow() {
    const t = document.getElementById('title').value;
    const b = document.getElementById('body').value;
    if (!t || !b) return alert('اكمل العنوان والنص');
    const data = getData(); data.title = t; data.body = b;
    fetch('/admin/smartads/segment-send-now', {
        method: 'POST', headers: {'Content-Type':'application/json', 'X-CSRF-TOKEN': CSRF},
        body: JSON.stringify(data)
    }).then(r => r.json()).then(d => {
        document.getElementById('result').innerHTML = d.success ? '✅ ' + d.message : '❌ ' + d.message;
    });
}
</script>
@endsection
<script>
fetch('/admin/notification-templates/api')
.then(r=>r.json())
.then(data=>{
    let opt='<option value="">-- اختر قالبا --</option>';
    data.forEach(t=>opt+='<option value="'+t.id+'">'+t.name+'</option>');
    document.getElementById('templateSelect').innerHTML=opt;
});
function loadTemplate(){
    const id=document.getElementById('templateSelect').value;
    if(!id)return;
    fetch('/admin/notification-templates/api')
    .then(r=>r.json())
    .then(data=>{
        const t=data.find(x=>x.id==id);
        if(t){ document.getElementById('title').value=t.title; document.getElementById('body').value=t.body; }
    });
}
function saveAsTemplate(){
    const name=prompt('اسم القالب:');
    if(!name)return;
    fetch('/admin/notification-templates',{
        method:'POST',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
        body:JSON.stringify({name:name, title:document.getElementById('title').value, body:document.getElementById('body').value})
    }).then(r=>r.json()).then(d=>{ if(d.success)alert('تم الحفظ'); });
}
</script>
