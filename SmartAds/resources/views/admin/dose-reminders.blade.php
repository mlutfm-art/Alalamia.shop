@extends('layouts.admin.app')
@section('title', 'تذكير الجرعات - لوحة التحكم')

@section('content')
<div class="content container-fluid">
    <div class="page-header mb-4">
        <h1 class="page-header-title">
            <i class="tio-medicine-outlined mr-2"></i>حملات تذكير الجرعات
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.smartads.index') }}">SmartAds</a></li>
                <li class="breadcrumb-item active">تذكير الجرعات</li>
            </ol>
        </nav>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    {{-- تنبيه الحملات الخاملة --}}
    @if(isset($staleCampaigns) && $staleCampaigns > 0)
    <div class="alert alert-warning d-flex align-items-center">
        <i class="tio-warning-outlined mr-2" style="font-size:1.4rem"></i>
        <span>يوجد {{ $staleCampaigns }} حملة نشطة لم تُرسل أي إشعارات خلال آخر 24 ساعة.</span>
    </div>
    @endif

    <!-- 📊 إحصائيات -->
    @php
        $sent   = $stats['total_sent'] ?? 0;
        $failed = $stats['total_failed'] ?? 0;
        $total  = $sent + $failed;
        $rate   = $total > 0 ? round($sent / $total * 100) : 0;
    @endphp
    <div class="row g-3 mb-3">
        <div class="col-4 col-md-2"><div class="card text-center shadow-sm"><div class="card-body py-3"><div class="text-muted small">نشطة</div><div class="h3 text-success mb-0">{{ $stats['active'] ?? 0 }}</div></div></div></div>
        <div class="col-4 col-md-2"><div class="card text-center shadow-sm"><div class="card-body py-3"><div class="text-muted small">متوقفة</div><div class="h3 text-secondary mb-0">{{ $stats['inactive'] ?? 0 }}</div></div></div></div>
        <div class="col-4 col-md-2"><div class="card text-center shadow-sm"><div class="card-body py-3"><div class="text-muted small">مستلمون</div><div class="h3 text-primary mb-0">{{ $stats['total_recipients'] ?? 0 }}</div></div></div></div>
        <div class="col-4 col-md-2"><div class="card text-center shadow-sm"><div class="card-body py-3"><div class="text-muted small">نشطون</div><div class="h3 text-info mb-0">{{ $stats['active_recipients'] ?? 0 }}</div></div></div></div>
        <div class="col-4 col-md-2"><div class="card text-center shadow-sm"><div class="card-body py-3"><div class="text-muted small">مكتملون</div><div class="h3 text-success mb-0">{{ $stats['completed'] ?? 0 }}</div></div></div></div>
        <div class="col-4 col-md-2"><div class="card text-center shadow-sm"><div class="card-body py-3"><div class="text-muted small">اليوم</div><div class="h3 text-warning mb-0">{{ $stats['today_sent'] ?? 0 }}</div></div></div></div>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3"><div class="card text-center shadow-sm"><div class="card-body py-3"><div class="text-muted small">✅ ناجح</div><div class="h3 text-success mb-0">{{ $sent }}</div></div></div></div>
        <div class="col-6 col-md-3"><div class="card text-center shadow-sm"><div class="card-body py-3"><div class="text-muted small">❌ فاشل</div><div class="h3 text-danger mb-0">{{ $failed }}</div></div></div></div>
        <div class="col-6 col-md-3"><div class="card text-center shadow-sm"><div class="card-body py-3"><div class="text-muted small">📊 معدل النجاح</div><div class="h3 text-primary mb-0">{{ $rate }}%</div></div></div></div>
        <div class="col-6 col-md-3"><div class="card text-center shadow-sm"><div class="card-body py-3"><div class="text-muted small">🔄 آخر تحديث</div><div class="small mb-0">{{ now()->format('H:i:s') }}</div></div></div></div>
    </div>

    <!-- 🧪 اختبار سريع -->
    <div class="card mb-3 border-warning">
        <div class="card-header bg-warning text-dark"><h5 class="mb-0">🧪 اختبار سريع - إرسال إشعار حقيقي بعد دقيقة</h5></div>
        <div class="card-body">
            <form action="{{ route('admin.smartads.test-scheduled.store') }}" method="POST" class="row g-2 align-items-end">
                @csrf
                <div class="col-md-5">
                    <label class="form-label fw-semibold small">اختر المنتج</label>
                    <select class="form-control select2-ajax" id="testProductSelect"
                            data-ajax-url="{{ route('admin.smartads.search-products') }}"
                            data-placeholder="ابحث عن منتج..."></select>
                    <input type="hidden" name="product_id" id="testProductId">
                    <input type="hidden" name="scheduled_at" value="{{ now()->addMinute()->format('Y-m-d H:i:s') }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-dark">
                        <i class="tio-timer-outlined mr-1"></i> إرسال بعد دقيقة
                    </button>
                </div>
            </form>
            <small class="text-muted">سيتم إرسال إشعار حقيقي لجميع مشتري هذا المنتج بعد دقيقة واحدة تلقائياً.</small>
        </div>
    </div>

    <div class="row">
        <!-- نموذج إنشاء حملة جديدة -->
        <div class="col-md-5">
            <div class="card">
                <div class="card-header"><h5 class="mb-0"><i class="tio-add-circle-outlined mr-2"></i>حملة جديدة</h5></div>
                <div class="card-body">
                    <form action="{{ route('admin.smartads.dose-reminders.store') }}" method="POST">
                        @csrf
                        <div class="mb-3"><label class="form-label fw-semibold">اسم الحملة <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label fw-semibold">المنتج <span class="text-danger">*</span></label><select class="form-control select2-ajax" id="productSelect" data-ajax-url="{{ route('admin.smartads.search-products') }}" data-placeholder="ابحث عن منتج..."></select><input type="hidden" name="product_id" id="productId"></div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">عنوان الإشعار <span class="text-danger">*</span></label>
                            <input type="text" name="notification_title" class="form-control" value="💊 تذكير الجرعة {dose_number} من {total_doses}" required>
                            <small class="text-muted">المتغيرات: {user_name}, {dose_number}, {total_doses}, {reminder_number}, {product_name}</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">نص الإشعار <span class="text-danger">*</span></label>
                            <textarea name="notification_body" class="form-control" rows="3" required>مرحباً {user_name}، حان وقت الجرعة رقم {dose_number} من {total_doses}. هذا التذكير رقم {reminder_number}.</textarea>
                            <small class="text-muted">المتغيرات: {user_name}, {dose_number}, {total_doses}, {reminder_number}, {product_name}</small>
                        </div>
                        <div class="row g-2">
                            <div class="col-4"><label class="form-label small">عدد الجرعات</label><input type="number" name="doses_count" class="form-control" value="1" min="1" required></div>
                            <div class="col-4"><label class="form-label small">أيام بين الجرعات</label><input type="number" name="days_between_doses" class="form-control" value="1" min="1" required></div>
                            <div class="col-4"><label class="form-label small">تذكيرات/جرعة</label><input type="number" name="reminders_per_dose" class="form-control" value="1" min="1" required></div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 mt-3"><i class="tio-save mr-1"></i>إنشاء الحملة</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- جدول الحملات + سجل العمليات -->
        <div class="col-md-7">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="tio-list-view mr-2"></i>سجل العمليات والحملات</h5>
                    <a href="{{ route('admin.smartads.dose-reminders') }}" class="btn btn-sm btn-outline-secondary">تحديث</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>الحملة</th>
                                    <th>المنتج</th>
                                    <th>الحالة</th>
                                    <th>المستلمون</th>
                                    <th>آخر إرسال</th>
                                    <th>إجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reminders as $r)
                                @php
                                    $lastLog = $r->logs()->latest()->first();
                                    $recipientCount = $r->recipients()->count();
                                    $completedCount = $r->recipients()->where('status', 'completed')->count();
                                @endphp
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.smartads.dose-reminders.show', $r->id) }}" class="fw-semibold">{{ $r->name }}</a>
                                        <div class="small text-muted">{{ $r->created_at->format('Y-m-d') }}</div>
                                    </td>
                                    <td>{{ $r->product?->name ?? '—' }}</td>
                                    <td>
                                        @if($r->is_active)
                                            <span class="badge badge-soft-success">نشط</span>
                                            <div class="small text-muted">{{ $recipientCount }} مستلم</div>
                                        @else
                                            <span class="badge badge-soft-secondary">متوقف</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-success">{{ $completedCount }} مكتمل</span>
                                        @if($recipientCount > 0)
                                            <div class="progress" style="height:4px">
                                                <div class="progress-bar bg-success" style="width:{{ round($completedCount / $recipientCount * 100) }}%"></div>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        @if($lastLog)
                                            <span class="small">{{ $lastLog->sent_at->format('Y-m-d H:i') }}</span>
                                            <br><span class="badge {{ $lastLog->status == 'sent' ? 'badge-soft-success' : 'badge-soft-danger' }}">{{ $lastLog->status == 'sent' ? 'نجح' : 'فشل' }}</span>
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.smartads.dose-reminders.show', $r->id) }}" class="btn btn-sm btn-info">👁️</a>
                                        <form action="{{ route('admin.smartads.dose-reminders.toggle', $r->id) }}" method="POST" style="display:inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm {{ $r->is_active ? 'btn-warning' : 'btn-success' }}">{{ $r->is_active ? '⏸️' : '▶️' }}</button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="6" class="text-center text-muted">لا توجد حملات.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $reminders->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- الإرساليات الفاشلة الأخيرة -->
    @if(isset($recentLogs) && $recentLogs->where('status', 'failed')->count() > 0)
    <div class="card mt-3 border-danger">
        <div class="card-header bg-danger text-white"><h5 class="mb-0">⚠️ آخر الإرساليات الفاشلة</h5></div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead><tr><th>الحملة</th><th>المستخدم</th><th>الجرعة</th><th>الخطأ</th><th>التاريخ</th></tr></thead>
                    <tbody>
                        @foreach($recentLogs->where('status', 'failed') as $log)
                        <tr>
                            <td>{{ $log->campaign->name ?? '—' }}</td>
                            <td>{{ $log->recipient?->user?->f_name ?? '—' }}</td>
                            <td>{{ $log->dose_number }}</td>
                            <td><small class="text-danger">{{ Str::limit($log->error_message, 50) }}</small></td>
                            <td>{{ $log->sent_at?->format('Y-m-d H:i') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>

@push('script')
<script>
if(typeof $.fn.select2 !== 'undefined'){
    $('.select2-ajax').each(function(){
        const $sel = $(this);
        const isTest = $sel.attr('id') === 'testProductSelect';
        $sel.select2({
            ajax:{ url:$sel.data('ajax-url'), dataType:'json', delay:250, data:function(p){return {q:p.term};}, processResults:function(d){return {results:d.results};}, cache:true },
            minimumInputLength:1, placeholder:$sel.data('placeholder')||'ابحث...',
            language:{noResults:function(){return "لا توجد نتائج";},searching:function(){return "جاري البحث...";},inputTooShort:function(){return "اكتب حرفين على الأقل";}}
        }).on('select2:select',function(e){
            if(isTest){ document.getElementById('testProductId').value = e.params.data.id; }
            else { document.getElementById('productId').value = e.params.data.id; }
        });
    });
}
</script>
@endpush
