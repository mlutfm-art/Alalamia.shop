@extends('layouts.admin.app')
@section('title', 'تعديل الإعلان: ' . $ad->title)
@section('content')
<div class="content container-fluid">
    <div class="page-header mb-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('admin.smartads.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="tio-arrow-backward"></i>
                </a>
                <div>
                    <h1 class="page-header-title mb-0">
                        <i class="tio-edit mr-2 text-primary"></i>
                        تعديل الإعلان #{{ $ad->id }}
                    </h1>
                    <small class="text-muted">{{ $ad->title }}</small>
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.smartads.analytics', $ad->id) }}" class="btn btn-outline-info btn-sm">
                    <i class="tio-chart-bar-3 mr-1"></i> تحليلات A/B
                </a>
                @if($ad->user_token)
                    <button type="button" id="sendPushBtn" class="btn btn-outline-success btn-sm"
                            data-token="{{ $ad->user_token }}"
                            data-title="{{ $ad->title }}"
                            data-body="{{ $ad->action_data['description'] ?? 'اشتراك جديد' }}">
                        <i class="tio-notifications-outlined mr-1"></i> إرسال إشعار
                    </button>
                @else
                    <button class="btn btn-outline-secondary btn-sm" disabled>
                        <i class="tio-notifications-outlined mr-1"></i> لا يوجد توكن
                    </button>
                @endif
                <button type="button" id="manual-push-btn" class="btn btn-outline-primary btn-sm"
                        data-id="{{ $ad->id }}">
                    <i class="tio-send mr-1"></i> إرسال للكل
                </button>
            </div>
        </div>
    </div>
    @if($ad->user_token)
        <div class="alert alert-info p-1 small">🔑 التوكن: {{ substr($ad->user_token, 0, 40) }}...</div>
    @else
        <div class="alert alert-warning p-1 small">⚠️ لا يوجد توكن لهذا الإعلان</div>
    @endif
    <div class="row g-3 mb-4">
        <div class="col-4 col-md-2">
            <div class="card text-center py-3 border-0 shadow-sm">
                <div class="text-muted small">الظهور</div>
                <div class="h4 fw-bold text-info mb-0">{{ number_format($ad->impressions) }}</div>
            </div>
        </div>
        <div class="col-4 col-md-2">
            <div class="card text-center py-3 border-0 shadow-sm">
                <div class="text-muted small">الضغطات</div>
                <div class="h4 fw-bold text-success mb-0">{{ number_format($ad->clicks) }}</div>
            </div>
        </div>
        <div class="col-4 col-md-2">
            <div class="card text-center py-3 border-0 shadow-sm">
                <div class="text-muted small">CTR%</div>
                <div class="h4 fw-bold text-warning mb-0">{{ $ad->ctr }}%</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-center py-3 border-0 shadow-sm">
                <div class="text-muted small">نوع الإجراء</div>
                <div class="fw-bold text-primary small mt-1">{{ $ad->action_data['type'] ?? '—' }}</div>
            </div>
        </div>
    </div>
    @if($errors->any())
        <div class="alert alert-danger mb-3">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif
    <form action="{{ route('admin.smartads.update', $ad->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @include('smartads::admin._form')
        <div class="card mt-3">
            <div class="card-body py-2">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="retrigger_push" id="retrigger_push" value="1">
                    <label class="form-check-label" for="retrigger_push">
                        <i class="tio-notifications-outlined mr-1 text-warning"></i>
                        إعادة إرسال Push Notification بعد حفظ التعديلات
                    </label>
                </div>
            </div>
        </div>
        <div class="mt-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary px-4">
                <i class="tio-save mr-1"></i> حفظ التعديلات
            </button>
            <a href="{{ route('admin.smartads.index') }}" class="btn btn-outline-secondary">إلغاء</a>
        </div>
    </form>
</div>
@push('script')
<script>
if (typeof $.fn.tooltip === 'undefined') { $.fn.tooltip = function() { return this; }; }

document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('sendPushBtn');
    if (!btn) return;
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        const token = this.dataset.token;
        const title = this.dataset.title;
        const body = this.dataset.body;
        if (!token) { alert('⚠️ لا يوجد توكن'); return; }
        if (!confirm(`إرسال إشعار: "${title}"؟`)) return;
        this.disabled = true;
        this.innerHTML = '⏳ جاري الإرسال...';
        fetch('/admin/send-push-direct', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
            },
            body: JSON.stringify({ token, title, body, link: 'https://alalamia.shop' })
        })
        .then(r => r.json())
        .then(d => {
            alert(d.success ? '✅ تم الإرسال!' : '❌ فشل: ' + (d.message || 'خطأ'));
            this.disabled = false;
            this.innerHTML = '📨 إرسال إشعار';
        })
        .catch(e => {
            alert('❌ خطأ: ' + e.message);
            this.disabled = false;
            this.innerHTML = '📨 إرسال إشعار';
        });
    });
});

document.getElementById('manual-push-btn')?.addEventListener('click', function() {
    const title = prompt('العنوان:', 'إشعار للكل');
    const body = prompt('النص:', 'رسالة لجميع المستخدمين');
    if (!title || !body) return;
    if (!confirm(`إرسال: "${title}" للجميع؟`)) return;
    this.disabled = true;
    this.innerHTML = '⏳ جاري...';
    fetch('/admin/send-to-all', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
        },
        body: JSON.stringify({ title, body })
    })
    .then(r => r.json())
    .then(d => {
        alert('✅ تم الإرسال!');
        this.disabled = false;
        this.innerHTML = '📨 إرسال للكل';
    })
    .catch(e => {
        alert('❌ خطأ: ' + e.message);
        this.disabled = false;
        this.innerHTML = '📨 إرسال للكل';
    });
});
</script>
@endpush
@endsection
