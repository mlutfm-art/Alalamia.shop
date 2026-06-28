@extends('layouts.admin.app')
@section('title', 'الإعلانات المجدولة')

@section('content')
<div class="content container-fluid">
    <div class="page-header mb-4">
        <h1 class="page-header-title"><i class="tio-calendar-note mr-2"></i>الإعلانات المجدولة</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.smartads.index') }}">SmartAds</a></li>
                <li class="breadcrumb-item active">الإعلانات المجدولة</li>
            </ol>
        </nav>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    <div class="row">
        <div class="col-md-5">
            <div class="card">
                <div class="card-header"><h5 class="mb-0"><i class="tio-add-circle-outlined mr-2"></i>جدولة إعلان جديد</h5></div>
                <div class="card-body">
                    <form action="{{ route('admin.smartads.schedules.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3"><label class="form-label fw-semibold">العنوان <span class="text-danger">*</span></label><input type="text" name="title" class="form-control" required maxlength="191"></div>
                        <div class="mb-3"><label class="form-label fw-semibold">النص <span class="text-danger">*</span></label><textarea name="body" class="form-control" rows="2" required maxlength="500"></textarea></div>
                        <div class="mb-3"><label class="form-label fw-semibold">الصورة (اختياري)</label><input type="file" name="image" class="form-control" accept="image/*"></div>
                        <div class="mb-3"><label class="form-label fw-semibold">موعد الإرسال <span class="text-danger">*</span></label><input type="datetime-local" name="scheduled_at" class="form-control" required></div>
                        <hr>
                        <h6 class="mb-3"><i class="tio-user-list mr-2"></i>الجمهور المستهدف</h6>
                        <div class="mb-3"><label class="form-label fw-semibold">نوع الجمهور</label><select name="target_type" id="targetType" class="form-control"><option value="all">👥 الجميع</option><option value="customer">👤 عميل محدد</option><option value="product">🛒 مشتري منتج</option><option value="category">📂 مهتم بقسم</option></select></div>
                        <div class="mb-3" id="customerSection" style="display:none"><label class="form-label fw-semibold">اختر العميل</label><select class="form-control select2-ajax" id="customerSelect" data-ajax-url="{{ route('admin.smartads.search-users') }}" data-placeholder="ابحث عن عميل..."></select><input type="hidden" name="customer_id" id="customerId"></div>
                        <div class="mb-3" id="productSection" style="display:none"><label class="form-label fw-semibold">اختر المنتج</label><select class="form-control select2-ajax" id="productSelect" data-ajax-url="{{ route('admin.smartads.search-products') }}" data-placeholder="ابحث عن منتج..."></select><input type="hidden" name="product_id" id="productId"></div>
                        <div class="mb-3" id="categorySection" style="display:none"><label class="form-label fw-semibold">اختر القسم</label><select name="category_id" class="form-control"><option value="">-- اختر --</option>@foreach($categories as $cat)<option value="{{ $cat->id }}">{{ $cat->name }}</option>@endforeach</select></div>
                        <button type="submit" class="btn btn-primary w-100"><i class="tio-schedule-outlined mr-1"></i>جدولة الإعلان</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <div class="card">
                <div class="card-header"><h5 class="mb-0"><i class="tio-list-view mr-2"></i>قائمة الإعلانات المجدولة</h5></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead><tr><th>العنوان</th><th>الجمهور</th><th>موعد الإرسال</th><th>الحالة</th><th>إجراءات</th></tr></thead>
                            <tbody>
                                @forelse($schedules as $schedule)
                                <tr>
                                    <td>{{ $schedule->title }}</td>
                                    <td>{{ $schedule->target_type }}</td>
                                    <td>{{ $schedule->scheduled_at->format('Y-m-d H:i') }}</td>
                                    <td>@if($schedule->status == 'pending')<span class="badge badge-soft-warning">قيد الانتظار</span>@elseif($schedule->status == 'sent')<span class="badge badge-soft-success">تم الإرسال</span>@else<span class="badge badge-soft-danger">فشل</span>@endif</td>
                                    <td>@if($schedule->status == 'pending')<form action="{{ route('admin.smartads.schedules.delete', $schedule->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد؟')">@csrf<button type="submit" class="btn btn-sm btn-danger">🗑️</button></form>@endif</td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="text-center text-muted">لا توجد إعلانات مجدولة.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $schedules->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

@push('script')
<script>
(function() {
    const targetTypeEl = document.getElementById('targetType');
    const customerSec  = document.getElementById('customerSection');
    const productSec   = document.getElementById('productSection');
    const categorySec  = document.getElementById('categorySection');
    function toggleTargetSections() {
        const val = targetTypeEl.value;
        customerSec.style.display = val === 'customer' ? '' : 'none';
        productSec.style.display  = val === 'product' ? '' : 'none';
        categorySec.style.display = val === 'category' ? '' : 'none';
    }
    targetTypeEl.addEventListener('change', toggleTargetSections);
    toggleTargetSections();

    if (typeof $.fn.select2 !== 'undefined') {
        $('.select2-ajax').each(function() {
            const $sel = $(this);
            $sel.select2({
                ajax:{ url:$sel.data('ajax-url'), dataType:'json', delay:250, data:function(p){return {q:p.term};}, processResults:function(d){return {results:d.results};}, cache:true },
                minimumInputLength:1, placeholder:$sel.data('placeholder')||'ابحث...',
                language:{noResults:function(){return "لا توجد نتائج";},searching:function(){return "جاري البحث...";},inputTooShort:function(){return "اكتب حرفين على الأقل";}}
            }).on('select2:select',function(e){
                const id = e.params.data.id;
                const targetId = $sel.attr('id') === 'customerSelect' ? 'customerId' : 'productId';
                document.getElementById(targetId).value = id;
            });
        });
    }
})();
</script>
@endpush
