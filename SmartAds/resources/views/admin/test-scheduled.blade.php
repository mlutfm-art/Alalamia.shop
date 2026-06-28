@extends('layouts.admin.app')
@section('title', 'اختبار الإرسال المجدول')

@section('content')
<div class="content container-fluid">
    <div class="page-header mb-4">
        <h1 class="page-header-title"><i class="tio-timer-outlined mr-2"></i>اختبار الإرسال المجدول</h1>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    <div class="card">
        <div class="card-header"><h5 class="mb-0">🧪 إرسال إشعار تجريبي بعد دقيقة واحدة (لمشتري منتج)</h5></div>
        <div class="card-body">
            <form action="{{ route('admin.smartads.test-scheduled.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-semibold">اختر المنتج <span class="text-danger">*</span></label>
                    <select class="form-control select2-ajax" id="productSelect"
                            data-ajax-url="{{ route('admin.smartads.search-products') }}"
                            data-placeholder="ابحث عن منتج..."></select>
                    <input type="hidden" name="product_id" id="productId">
                    <small class="text-muted">سيتم إرسال الإشعار لجميع العملاء الذين اشتروا هذا المنتج</small>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="tio-timer-outlined mr-1"></i> جدولة إشعار تجريبي (بعد دقيقة)
                </button>
            </form>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header"><h5 class="mb-0">📋 كيف يعمل؟</h5></div>
        <div class="card-body">
            <ol>
                <li>اختر أي منتج.</li>
                <li>اضغط "جدولة إشعار تجريبي".</li>
                <li>انتظر دقيقة واحدة.</li>
                <li>سيتم الإرسال تلقائياً (أو شغّل <code>php artisan smartads:send-scheduled</code>).</li>
            </ol>
        </div>
    </div>
</div>

@push('script')
<script>
if(typeof $.fn.select2 !== 'undefined'){
    $('.select2-ajax').each(function(){
        const $sel = $(this);
        $sel.select2({
            ajax:{ url:$sel.data('ajax-url'), dataType:'json', delay:250, data:function(p){return {q:p.term};}, processResults:function(d){return {results:d.results};}, cache:true },
            minimumInputLength:1, placeholder:$sel.data('placeholder')||'ابحث...',
            language:{noResults:function(){return "لا توجد نتائج";},searching:function(){return "جاري البحث...";},inputTooShort:function(){return "اكتب حرفين على الأقل";}}
        }).on('select2:select',function(e){ document.getElementById('productId').value = e.params.data.id; });
    });
}
</script>
@endpush
