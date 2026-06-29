@extends('layouts.admin.app')
@section('title', 'إدارة الإعلانات الذكية - SmartAds')

@section('content')
<div class="content container-fluid text-right" dir="rtl">
    <div class="page-header mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="page-header-title"><i class="tio-chart-bar-3 mr-2 text-primary"></i>محرك الإعلانات والاشعارات الذكي</h1>
            <a href="{{ route('admin.smartads.create') }}" class="btn btn-primary"><i class="tio-add mr-1"></i> إنشاء حملة جديدة</a>
        </div>
    </div>

    {{-- 🚀 أزرار الخدمات المتقدمة --}}
    <div class="row g-2 mb-4">
        <div class="col-md-3">
            <a href="{{ route('admin.smartads.dose-reminders') }}" class="btn btn-outline-success w-100 py-3 shadow-sm border-2">
                <i class="tio-medicine-outlined mb-2 d-block" style="font-size: 24px"></i>
                <strong>تذكير الجرعات</strong>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('admin.smartads.groups') }}" class="btn btn-outline-primary w-100 py-3 shadow-sm border-2">
                <i class="tio-group mb-2 d-block" style="font-size: 24px"></i>
                <strong>إرسال للمجموعات</strong>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('admin.smartads.occasions') }}" class="btn btn-outline-danger w-100 py-3 shadow-sm border-2">
                <i class="tio-calendar-event mb-2 d-block" style="font-size: 24px"></i>
                <strong>المناسبات والأعياد</strong>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('admin.smartads.segment-send') }}" class="btn btn-outline-info w-100 py-3 shadow-sm border-2">
                <i class="tio-send-outlined mb-2 d-block" style="font-size: 24px"></i>
                <strong>إرسال حسب الفئة</strong>
            </a>
        </div>
    </div>

    {{-- 📊 شريط الإحصائيات --}}
    <div class="row g-3 mb-4">
        @foreach([
            'total' => ['الإجمالي', 'primary', 'tio-visible'],
            'active' => ['النشطة', 'success', 'tio-checkmark-circle'],
            'total_sent' => ['الاشعارات المرسلة', 'info', 'tio-send'],
            'impressions' => ['مشاهدات التطبيق', 'warning', 'tio-hidden'],
            'clicks' => ['النقرات', 'danger', 'tio-click']
        ] as $key => $val)
        <div class="col-md">
            <div class="card text-center border-0 shadow-sm">
                <div class="card-body py-3">
                    <div class="text-{{ $val[1] }} mb-2"><i class="{{ $val[2] }} fa-2x"></i></div>
                    <div class="text-muted small mb-1">{{ $val[0] }}</div>
                    <div class="h3 mb-0 font-weight-bold">{{ number_format($stats[$key] ?? 0) }}</div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- 📋 جدول الإعلانات --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom">
            <h5 class="mb-0">قائمة الحملات الإعلانية</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-borderless table-thead-bordered">
                <thead class="thead-light">
                    <tr>
                        <th>#</th>
                        <th>الإعلان</th>
                        <th>النوع</th>
                        <th>الاستهداف</th>
                        <th>التفاعل</th>
                        <th>الحالة</th>
                        <th class="text-center">إجراءات التحكم</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ads as $ad)
                    <tr>
                        <td>{{ $ad->id }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                @if($ad->image)
                                    <img src="{{ $ad->image_url }}" class="rounded mr-2" width="45" height="45" style="object-fit: cover; border: 1px solid #eee">
                                @endif
                                <div class="mr-2">
                                    <div class="font-weight-bold text-dark">{{ $ad->title }}</div>
                                    <small class="text-muted">{{ $ad->placement }}</small>
                                </div>
                            </div>
                        </td>
                        <td><span class="badge badge-soft-info">{{ $ad->ad_type }}</span></td>
                        <td>
                            <small class="d-block text-muted">📍 {{ $ad->target_region ?? 'الكل' }}</small>
                            <small class="d-block text-muted">📱 {{ $ad->device_type ?? 'الكل' }}</small>
                        </td>
                        <td>
                            <div class="text-primary small">👁️ {{ number_format($ad->impressions) }}</div>
                            <div class="text-success small">🖱️ {{ number_format($ad->clicks) }}</div>
                        </td>
                        <td>
                            <label class="switcher mx-auto">
                                <input type="checkbox" class="switcher_input" onchange="toggleStatus({{ $ad->id }}, this.checked ? 1 : 0)" {{ $ad->status ? 'checked' : '' }}>
                                <span class="switcher_control"></span>
                            </label>
                        </td>
                        <td>
                            <div class="d-flex justify-content-center gap-1">
                                {{-- زر المعاينة --}}
                                <button onclick="previewAd({{ $ad->id }})" class="btn btn-sm btn-soft-primary" title="معاينة"><i class="tio-invisible"></i> معاينة</button>
                                
                                {{-- زر التعديل --}}
                                <a href="{{ route('admin.smartads.edit', $ad->id) }}" class="btn btn-sm btn-outline-primary" title="تعديل"><i class="tio-edit"></i> تعديل</a>
                                
                                {{-- زر الإرسال --}}
                                <button onclick="sendPush({{ $ad->id }})" class="btn btn-sm btn-success" title="إرسال إشعار"><i class="tio-send"></i> إرسال</button>
                                
                                {{-- زر التقارير --}}
                                <a href="{{ route('admin.smartads.analytics', $ad->id) }}" class="btn btn-sm btn-soft-info" title="تقارير"><i class="tio-chart-bar-4"></i></a>
                                
                                {{-- زر الحذف --}}
                                <button onclick="deleteAd({{ $ad->id }})" class="btn btn-sm btn-outline-danger" title="حذف"><i class="tio-delete"></i></button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center p-5 text-muted">لا توجد حملات إعلانية حالياً، ابدأ بإنشاء واحدة!</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white border-0">{{ $ads->links() }}</div>
    </div>
</div>

{{-- مودال المعاينة (Ad Preview) --}}
<div class="modal fade" id="previewModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg border-0" style="border-radius: 20px">
            <div class="modal-header border-0">
                <h5 class="modal-title">معاينة الإعلان على التطبيق</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body text-center p-4">
                <div id="previewContent">
                    {{-- سيتم تعبئته عبر JS --}}
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleStatus(id, status) {
        fetch('{{ route("admin.smartads.status-update") }}', {
            method: 'POST',
            headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
            body: JSON.stringify({id: id, status: status})
        }).then(r => r.json()).then(data => {
            if(data.success) toastr.success('تم تحديث حالة الحملة بنجاح');
        });
    }

    function sendPush(id) {
        if(!confirm('هل تريد إرسال هذا الإعلان كإشعار فوري لجميع المستخدمين المستهدفين الآن؟')) return;
        toastr.info('جاري معالجة الإرسال...');
        fetch('{{ route("admin.smartads.send-firebase") }}', {
            method: 'POST',
            headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
            body: JSON.stringify({id: id})
        }).then(r => r.json()).then(data => {
            if(data.success) {
                toastr.success(data.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                toastr.error(data.message || 'فشل الإرسال، تأكد من إعدادات Firebase');
            }
        });
    }

    function deleteAd(id) {
        if(!confirm('تحذير: سيتم حذف الحملة وكافة إحصائياتها نهائياً. هل أنت متأكد؟')) return;
        fetch('{{ route("admin.smartads.delete") }}', {
            method: 'POST',
            headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
            body: JSON.stringify({id: id})
        }).then(r => r.json()).then(data => {
            if(data.success) {
                toastr.success('تم الحذف بنجاح');
                location.reload();
            }
        });
    }

    function previewAd(id) {
        toastr.info('جاري جلب بيانات المعاينة...');
        // في مشروع حقيقي، سنقوم بجلب بيانات الإعلان عبر AJAX هنا. 
        // للتبسيط، سنعرض رسالة نجاح وافتراض نجاح العملية.
        $('#previewModal').modal('show');
        $('#previewContent').html('<div class="spinner-border text-primary"></div>');
        
        fetch('{{ url("admin/smartads/edit") }}/' + id)
            .then(r => r.text())
            .then(html => {
               // هنا يمكن استخراج الصورة والعنوان لعرضهم في المودال
               $('#previewContent').html('<i class="tio-smartphone fa-5x text-muted mb-3"></i><br><p>تتم المعاينة حالياً من خلال تطبيق الجوال الفعلي للتأكد من دقة الظهور.</p>');
            });
    }
</script>

<style>
    .border-2 { border-width: 2px !important; }
    .badge-soft-info { background-color: #e7f6f8; color: #01778e; border: 1px solid #d1eff2; }
    .btn-soft-primary { background-color: #e7f3ff; color: #007bff; }
    .btn-soft-info { background-color: #e3fbff; color: #008fa1; }
    .gap-1 { gap: 5px; }
    .card { transition: transform 0.2s; }
    .card:hover { transform: translateY(-3px); }
</style>
@endsection
