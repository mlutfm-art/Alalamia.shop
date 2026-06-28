@extends('layouts.admin.app')
@section('title', 'المناسبات والأعياد - لوحة التحكم')

@push('css_or_js')
<style>
.occasion-card { transition: all .2s; border-radius: 12px; }
.occasion-card:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,0,0,.08); }
.type-badge { font-size: 12px; padding: 4px 12px; border-radius: 20px; }
</style>
@endpush

@section('content')
<div class="content container-fluid">
    <div class="page-header mb-4">
            <a href="{{ route('admin.smartads.index') }}" class="btn btn-outline-secondary btn-sm"><i class="tio-arrow-backward mr-1"></i> العودة لـ SmartAds</a>
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <h1 class="page-header-title mb-0">
                    <i class="tio-calendar-event mr-2 text-primary"></i>المناسبات والأعياد
                </h1>
                <small class="text-muted">إدارة التهاني التلقائية في المناسبات الدينية والوطنية</small>
            </div>
            @if(isset($nextOccasion) && $nextOccasion)
            <div class="alert alert-info mb-0 py-2 px-3">
                🗓️ المناسبة القادمة: <strong>{{ $nextOccasion->name }}</strong> - {{ $nextOccasion->date->format('Y/m/d') }}
            </div>
            @endif
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.smartads.index') }}">SmartAds</a></li>
                <li class="breadcrumb-item active">المناسبات</li>
            </ol>
        </nav>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    <div class="row g-3 mb-4">
        <div class="col-3"><div class="card text-center"><div class="card-body py-2"><div class="text-muted small">دينية</div><div class="h3 mb-0">{{ $occasions->where('type','religious')->count() }}</div></div></div></div>
        <div class="col-3"><div class="card text-center"><div class="card-body py-2"><div class="text-muted small">وطنية</div><div class="h3 mb-0">{{ $occasions->where('type','national')->count() }}</div></div></div></div>
        <div class="col-3"><div class="card text-center"><div class="card-body py-2"><div class="text-muted small">اجتماعية</div><div class="h3 mb-0">{{ $occasions->where('type','social')->count() }}</div></div></div></div>
        <div class="col-3"><div class="card text-center"><div class="card-body py-2"><div class="text-muted small">نشطة</div><div class="h3 text-success mb-0">{{ $occasions->where('is_active',true)->count() }}</div></div></div></div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card occasion-card">
                <div class="card-header"><h5 class="mb-0"><i class="tio-add-circle-outlined mr-2"></i>إضافة مناسبة</h5></div>
                <div class="card-body">
                    <form action="{{ route('admin.smartads.occasions.store') }}" method="POST">
                        @csrf
                        <div class="mb-3"><label class="form-label fw-semibold">اسم المناسبة <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" required placeholder="مثال: عيد الأضحى المبارك"></div>
                        <div class="mb-3"><label class="form-label fw-semibold">النوع</label><select name="type" class="form-control"><option value="religious">🕌 دينية</option><option value="national">🇾🇪 وطنية</option><option value="social">🎉 اجتماعية</option><option value="other">📌 أخرى</option></select></div>
                        <div class="mb-3"><label class="form-label fw-semibold">التاريخ <span class="text-danger">*</span></label><input type="date" name="date" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label fw-semibold">عنوان الإشعار <span class="text-danger">*</span></label><input type="text" name="notification_title" class="form-control" required placeholder="🎉 عيد أضحى مبارك"></div>
                        <div class="mb-3"><label class="form-label fw-semibold">نص الإشعار <span class="text-danger">*</span></label><textarea name="notification_body" class="form-control" rows="3" required placeholder="بمناسبة عيد الأضحى المبارك..."></textarea></div>
                        <div class="row g-2">
                            <div class="col-6"><label class="form-label small">الإرسال قبل (أيام)</label><input type="number" name="send_before_days" class="form-control" value="0" min="0"></div>
                            <div class="col-6"><label class="form-label small">وقت الإرسال</label><input type="time" name="send_at" class="form-control" value="08:00"></div>
                        </div>
                        <div class="form-check mt-2"><input type="checkbox" name="recurring" value="1" class="form-check-input" checked><label class="form-check-label small">سنوي (يتكرر كل عام)</label></div>
                        <button type="submit" class="btn btn-primary w-100 mt-3"><i class="tio-save mr-1"></i>حفظ المناسبة</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="tio-list-view mr-2"></i>قائمة المناسبات ({{ $occasions->total() }})</h5>
                    <span class="badge badge-soft-primary">{{ $occasions->where('sent_this_year',false)->where('is_active',true)->count() }} معلقة</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm mb-0">
                            <thead class="table-light">
                                <tr><th>المناسبة</th><th>النوع</th><th>التاريخ</th><th>الحالة</th><th>أُرسلت</th><th>إجراءات</th></tr>
                            </thead>
                            <tbody>
                                @forelse($occasions as $o)
                                <tr>
                                    <td>
                                        <strong>{{ $o->name }}</strong>
                                        <div class="small text-muted">{{ Str::limit($o->notification_title, 30) }}</div>
                                    </td>
                                    <td><span class="type-badge" style="background:#f0f0f0">{{ $types[$o->type] ?? $o->type }}</span></td>
                                    <td>{{ $o->date->format('Y/m/d') }}<div class="small text-muted">{{ $o->send_at }}</div></td>
                                    <td><span class="badge badge-{{ $o->is_active ? 'soft-success' : 'soft-secondary' }}">{{ $o->is_active ? 'نشط' : 'متوقف' }}</span></td>
                                    <td>{{ $o->sent_this_year ? '✅' : '⏳' }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <form action="{{ route('admin.smartads.occasions.toggle', $o->id) }}" method="POST">@csrf<button class="btn btn-sm {{ $o->is_active ? 'btn-warning' : 'btn-success' }}" title="{{ $o->is_active ? 'إيقاف' : 'تفعيل' }}">{{ $o->is_active ? '⏸️' : '▶️' }}</button></form>
                                            @if($o->sent_this_year)
                                            <form action="{{ route('admin.smartads.occasions.reset', $o->id) }}" method="POST">@csrf<button class="btn btn-sm btn-info" title="إعادة للإرسال">🔄</button></form>
                                            @endif
                                            <form action="{{ route('admin.smartads.occasions.delete', $o->id) }}" method="POST" onsubmit="return confirm('حذف {{ $o->name }}؟')">@csrf @method('DELETE')<button class="btn btn-sm btn-danger">🗑️</button></form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="6" class="text-center py-3 text-muted">لا توجد مناسبات</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">{{ $occasions->links() }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
