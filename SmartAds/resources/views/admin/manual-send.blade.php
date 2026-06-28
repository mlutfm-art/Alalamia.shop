@extends('layouts.admin.app')
@section('title', 'إرسال إشعار يدوي')

@section('content')
<div class="content container-fluid">
    <div class="page-header mb-4">
        <h1 class="page-header-title">
            <i class="tio-send-outlined mr-2"></i>إرسال إشعار يدوي
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.smartads.index') }}">SmartAds</a></li>
                <li class="breadcrumb-item active">إرسال يدوي</li>
            </ol>
        </nav>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.smartads.manual-send.submit') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">العنوان <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" required maxlength="191">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">النص <span class="text-danger">*</span></label>
                        <textarea name="body" class="form-control" rows="2" required maxlength="500"></textarea>
                    </div>
                </div>

                <div class="row g-3 mt-2">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">الصورة (اختياري)</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">الرابط (Deep Link)</label>
                        <input type="text" name="deep_link" class="form-control" placeholder="اختياري: رابط الشاشة داخل التطبيق">
                    </div>
                </div>

                <hr class="mt-4">
                <h5 class="mb-3"><i class="tio-user-list mr-2"></i>الجمهور المستهدف</h5>
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">نوع الجمهور</label>
                        <select name="target_type" id="targetType" class="form-control form-select-lg">
                            <option value="all">👥 الجميع</option>
                            <option value="customer">👤 عميل محدد</option>
                            <option value="product">🛒 مشتري منتج</option>
                            <option value="category">📂 مهتم بقسم</option>
                        </select>
                    </div>

                    <div class="col-md-6" id="customerSection" style="display:none">
                        <label class="form-label fw-semibold"><i class="tio-search mr-1"></i>اختر العميل</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="tio-user-outlined"></i></span>
                            <select class="form-control select2-ajax" id="customerSelect"
                                    data-ajax-url="{{ route('admin.smartads.search-users') }}"
                                    data-placeholder="ابحث عن عميل...">
                            </select>
                        </div>
                        <input type="hidden" name="customer_id" id="customerId">
                    </div>

                    <div class="col-md-6" id="productSection" style="display:none">
                        <label class="form-label fw-semibold">اختر المنتج</label>
                        <select class="form-control select2-ajax" id="productSelect"
                                data-ajax-url="{{ route('admin.smartads.search-products') }}"
                                data-placeholder="ابحث عن منتج...">
                        </select>
                        <input type="hidden" name="product_id" id="productId">
                    </div>

                    <div class="col-md-4" id="categorySection" style="display:none">
                        <label class="form-label fw-semibold">اختر القسم</label>
                        <select name="category_id" class="form-control">
                            <option value="">-- اختر --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mt-3">
                    <button type="button" id="previewTargetBtn" class="btn btn-outline-info btn-sm">
                        <i class="tio-eye mr-1"></i> معاينة العدد
                    </button>
                    <span id="previewResult" class="text-muted ms-2 small"></span>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary"><i class="tio-send-outlined mr-1"></i>إرسال الإشعار</button>
                </div>
            </form>
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
            const url = $sel.data('ajax-url');
            $sel.select2({
                ajax: {
                    url: url,
                    dataType: 'json',
                    delay: 250,
                    data: function(params) { return { q: params.term }; },
                    processResults: function(data) { return { results: data.results }; },
                    cache: true
                },
                minimumInputLength: 1,
                placeholder: $sel.data('placeholder') || 'ابحث...',
                language: {
                    noResults: function() { return "لا توجد نتائج"; },
                    searching: function() { return "جاري البحث..."; },
                    inputTooShort: function() { return "اكتب حرفين على الأقل"; }
                },
                templateResult: function(item) {
                    if (!item.id) return item.text;
                    var html = '<div class="d-flex flex-column">';
                    html += '<span class="fw-bold">' + (item.name || item.text) + '</span>';
                    if (item.email) html += '<small class="text-muted">' + item.email + '</small>';
                    if (item.phone) html += '<small class="text-muted">' + item.phone + '</small>';
                    html += '</div>';
                    return $(html);
                },
                templateSelection: function(item) {
                    return item.name || item.text;
                }
            }).on('select2:select', function(e) {
                const id = e.params.data.id;
                const targetId = $sel.attr('id') === 'customerSelect' ? 'customerId' : 'productId';
                document.getElementById(targetId).value = id;
            });
        });
    }

    document.getElementById('previewTargetBtn').addEventListener('click', function() {
        const payload = {
            target_type: targetTypeEl.value,
            _token: '{{ csrf_token() }}'
        };
        if (payload.target_type === 'customer') payload.customer_id = document.getElementById('customerId').value;
        if (payload.target_type === 'product') payload.product_id = document.getElementById('productId').value;
        if (payload.target_type === 'category') payload.category_id = document.querySelector('[name="category_id"]').value;

        fetch('{{ route("admin.smartads.preview-target") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(r => r.json())
        .then(data => {
            document.getElementById('previewResult').innerHTML =
                '👥 إجمالي المستخدمين: ' + data.total_users + ' | 📱 أجهزة مسجلة: ' + data.fcm_count;
        });
    });
})();
</script>
@endpush
