{{-- ══════════════════════════════════════════════════════════════
     Smart Engagement Engine — Shared Form Partial (تصميم محسّن)
══════════════════════════════════════════════════════════════ --}}
@php
    $ad  = $ad  ?? null;
    $ac  = $ad?->action_data ?? [];
    $p   = $ac['payload'] ?? [];
    $currentActionType = $ac['type'] ?? '';
    $targetType = $ac['target_type'] ?? 'all';
    $targetValue = $ac['target_value'] ?? [];
@endphp

{{-- ══ القسم 1: المعلومات الأساسية ══════════════════════════ --}}
<div class="card mb-3">
    <div class="card-header"><h5 class="mb-0"><i class="tio-settings-outlined mr-2"></i>المعلومات الأساسية</h5></div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold">العنوان <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control" value="{{ old('title', $ad->title ?? '') }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">نوع الإعلان <span class="text-danger">*</span></label>
                <select name="ad_type" id="ad_type" class="form-control" required>
                    @foreach(config('smartads.ad_types') as $t)
                        <option value="{{ $t }}" @selected(old('ad_type', $ad->ad_type ?? 'banner') === $t)>
                            {{ ucfirst($t) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">الموضع <span class="text-danger">*</span></label>
                <select name="placement" class="form-control" required>
                    @foreach(config('smartads.placements') as $p_)
                        <option value="{{ $p_ }}" @selected(old('placement', $ad->placement ?? '') === $p_)>{{ $p_ }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="row g-3 mt-1">
            <div class="col-md-6">
                <label class="form-label fw-semibold">صورة الإعلان</label>
                <input type="file" name="image" class="form-control" accept="image/*">
                @if(!empty($ad?->image_url))
                    <div class="mt-2">
                        <img src="{{ $ad->image_url }}" style="height:70px;border-radius:8px;object-fit:cover">
                        <small class="d-block text-muted">الصورة الحالية</small>
                    </div>
                @endif
            </div>
            <div class="col-md-6" id="video-fields" style="display:none">
                <label class="form-label fw-semibold">ملف الفيديو (mp4/webm، أقصى 20MB)</label>
                <input type="file" name="video_file" class="form-control" accept="video/*">
                <label class="form-label fw-semibold mt-2">أو رابط فيديو خارجي (YouTube/Vimeo)</label>
                <input type="text" name="video_url" class="form-control"
                       value="{{ old('video_url', !empty($ad?->video) && str_starts_with($ad->video,'http') ? $ad->video : '') }}"
                       placeholder="https://...">
            </div>
        </div>

        <div class="row g-3 mt-1">
            <div class="col-md-4">
                <label class="form-label fw-semibold">نص الزر</label>
                <input type="text" name="button_text" class="form-control"
                       value="{{ old('button_text', $ac['button_text'] ?? 'اعرف أكثر') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">عنوان فرعي</label>
                <input type="text" name="subtitle" class="form-control"
                       value="{{ old('subtitle', $ac['subtitle'] ?? '') }}" placeholder="جملة قصيرة جاذبة...">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">وصف (للإعلانات الـ Native)</label>
                <input type="text" name="description" class="form-control"
                       value="{{ old('description', $ac['description'] ?? '') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">لون الخلفية</label>
                <div class="d-flex align-items-center gap-2">
                    <input type="color" name="background_color" class="form-control form-control-color"
                           value="{{ old('background_color', $ac['background_color'] ?? '#ffffff') }}" style="width:50px;height:38px">
                    <input type="text" id="bg_hex" class="form-control form-control-sm"
                           value="{{ old('background_color', $ac['background_color'] ?? '#ffffff') }}">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">لون النص</label>
                <div class="d-flex align-items-center gap-2">
                    <input type="color" name="text_color" class="form-control form-control-color"
                           value="{{ old('text_color', $ac['text_color'] ?? '#212121') }}" style="width:50px;height:38px">
                    <input type="text" id="txt_hex" class="form-control form-control-sm"
                           value="{{ old('text_color', $ac['text_color'] ?? '#212121') }}">
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══ القسم 2: الإجراء التفاعلي ══════════════════════════════ --}}
<div class="card mb-3">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0"><i class="tio-flash-outlined mr-2 text-warning"></i>الإجراء التفاعلي (Action)</h5>
        <small class="text-muted">هذا هو العمل الذي يحدث عند ضغط المستخدم على الإعلان</small>
    </div>
    <div class="card-body">
        <div class="row g-3 mb-3">
            @php $allTypes = \Modules\SmartAds\app\Services\ActionResolverService::supportedTypes(); @endphp
            @foreach($allTypes as $groupKey => $groupTypes)
                <div class="col-md-4">
                    <div class="card border h-100 action-group-card" data-group="{{ $groupKey }}">
                        <div class="card-header py-2" style="font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;color:#555">
                            @php
                                $groupIcons = [
                                    'internal_navigation' => '🧭',
                                    'social_follow'       => '👥',
                                    'social_interact'     => '❤️',
                                    'contact'             => '📱',
                                    'interactive'         => '⚡',
                                    'external_media'      => '🌐',
                                ];
                            @endphp
                            {{ $groupIcons[$groupKey] ?? '•' }} {{ str_replace('_', ' ', $groupKey) }}
                        </div>
                        <div class="card-body py-2 px-2">
                            @foreach($groupTypes as $typeVal => $typeLabel)
                                <div class="form-check action-type-option mb-1">
                                    <input class="form-check-input action-type-radio" type="radio"
                                           name="action_type" id="at_{{ $typeVal }}"
                                           value="{{ $typeVal }}"
                                           {{ old('action_type', $currentActionType) === $typeVal ? 'checked' : '' }}>
                                    <label class="form-check-label small" for="at_{{ $typeVal }}">
                                        {{ $typeLabel }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div id="action-fields-wrapper">
            {{-- بقية الحقول الديناميكية للإجراءات كما هي --}}
        </div>
    </div>
</div>

{{-- ══ القسم 3: الجمهور المستهدف (تصميم محسّن) ══════════════════════════ --}}
<div class="card mb-3 border-0 shadow-sm">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="tio-user-list mr-2"></i>الجمهور المستهدف (اختياري)</h5>
        <small class="text-white-50">حدد شريحة محددة لإرسال الإعلان إليها. اتركه "الجميع" للإرسال لكل المستخدمين.</small>
    </div>
    <div class="card-body">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label fw-semibold"><i class="tio-folder-outlined mr-1"></i>نوع الجمهور</label>
                <select name="target_type" id="targetType" class="form-control form-select-lg">
                    <option value="all" {{ $targetType === 'all' ? 'selected' : '' }}>👥 الجميع</option>
                    <option value="customer" {{ $targetType === 'customer' ? 'selected' : '' }}>👤 عميل محدد</option>
                    <option value="product" {{ $targetType === 'product' ? 'selected' : '' }}>🛒 مشتري منتج</option>
                    <option value="category" {{ $targetType === 'category' ? 'selected' : '' }}>📂 مهتم بقسم</option>
                </select>
            </div>

            <div class="col-md-6" id="customerSection" style="display:none">
                <label class="form-label fw-semibold"><i class="tio-search mr-1"></i>اختر العميل</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="tio-user-outlined"></i></span>
                    <select class="form-control select2-ajax" id="customerSelect"
                            data-ajax-url="{{ route('admin.smartads.search-users') }}"
                            data-placeholder="ابحث عن عميل بالاسم أو البريد أو الهاتف..."
                            style="width:100%">
                        @if(isset($selectedUsers) && $selectedUsers->isNotEmpty())
                            @foreach($selectedUsers as $user)
                                <option value="{{ $user->id }}" selected>
                                    {{ $user->f_name }} {{ $user->l_name }} ({{ $user->email ?? $user->phone }})
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <input type="hidden" name="target_value[customer_id]" id="customerId"
                       value="{{ $targetValue['customer_id'] ?? '' }}">
                <small class="text-muted">ابحث عن العميل واختره من القائمة المنسدلة.</small>
            </div>

            <div class="col-md-6" id="productSection" style="display:none">
                <label class="form-label fw-semibold"><i class="tio-shopping-cart-outlined mr-1"></i>اختر المنتج</label>
                <select class="form-control select2-ajax" id="productSelect"
                        data-ajax-url="{{ route('admin.smartads.search-products') }}"
                        data-placeholder="ابحث عن منتج...">
                </select>
                <input type="hidden" name="target_value[product_id]" id="productId"
                       value="{{ $targetValue['product_id'] ?? '' }}">
            </div>

            <div class="col-md-4" id="categorySection" style="display:none">
                <label class="form-label fw-semibold"><i class="tio-category-outlined mr-1"></i>اختر القسم</label>
                <select name="target_value[category_id]" class="form-control form-select-lg">
                    <option value="">-- اختر --</option>
                    @foreach(($categories ?? []) as $cat)
                        <option value="{{ $cat->id }}"
                            {{ ($targetValue['category_id'] ?? '') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mt-3 d-flex align-items-center">
            <button type="button" id="previewTargetBtn" class="btn btn-outline-info btn-sm me-2">
                <i class="tio-eye mr-1"></i> معاينة العدد
            </button>
            <span id="previewResult" class="text-muted ms-2 small"></span>
        </div>
    </div>
</div>

{{-- ══ القسم 4: الاستهداف والجدولة (الأصلي) ══════════════════════════ --}}
<div class="row g-3">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header"><h6 class="mb-0"><i class="tio-filter-list mr-2"></i>الاستهداف</h6></div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-12">
                        <label class="form-label small fw-semibold">القسم المستهدف</label>
                        <select name="target_category_id" class="form-control form-control-sm">
                            <option value="">— الكل —</option>
                            @foreach(($categories ?? []) as $cat)
                                <option value="{{ $cat->id }}"
                                    @selected(($ad->target_category_id ?? null) == $cat->id)>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">المنطقة الجغرافية</label>
                        <input type="text" name="target_region" class="form-control form-control-sm"
                               value="{{ $ad->target_region ?? '' }}" placeholder="SA, AE, EG ...">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">الجهاز</label>
                        <select name="device_type" class="form-control form-control-sm">
                            @foreach(config('smartads.device_types') as $d)
                                <option value="{{ $d }}" @selected(($ad->device_type ?? 'all') === $d)>{{ $d }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header"><h6 class="mb-0"><i class="tio-calendar-note mr-2"></i>الجدولة الزمنية</h6></div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">تاريخ البدء</label>
                        <input type="datetime-local" name="start_at" class="form-control form-control-sm"
                               value="{{ old('start_at', isset($ad->start_at) ? $ad->start_at->format('Y-m-d\TH:i') : '') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">تاريخ الانتهاء</label>
                        <input type="datetime-local" name="end_at" class="form-control form-control-sm"
                               value="{{ old('end_at', isset($ad->end_at) ? $ad->end_at->format('Y-m-d\TH:i') : '') }}">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══ القسم 5: اختبار A/B ════════════════════════════════ --}}
<div class="card mt-3">
    <div class="card-header">
        <h6 class="mb-0"><i class="tio-split-horizontal mr-2"></i>اختبار A/B (اختياري)</h6>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label small fw-semibold">الإعلان الأب (اجعل هذا متغيراً تجريبياً)</label>
                <select name="parent_id" class="form-control form-control-sm">
                    <option value="">— إعلان مستقل —</option>
                    @foreach(($parents ?? []) as $par)
                        <option value="{{ $par->id }}" @selected(($ad->parent_id ?? null) == $par->id)>
                            #{{ $par->id }} — {{ $par->title }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">تسمية المتغير</label>
                <select name="ab_variant" class="form-control form-control-sm">
                    <option value="">—</option>
                    <option value="A" @selected(($ad->ab_variant ?? '') === 'A')>A</option>
                    <option value="B" @selected(($ad->ab_variant ?? '') === 'B')>B</option>
                    <option value="C" @selected(($ad->ab_variant ?? '') === 'C')>C</option>
                </select>
            </div>
        </div>
    </div>
</div>

@push('script')
<script>
(function() {
    const adTypeEl = document.getElementById('ad_type');
    const videoFields = document.getElementById('video-fields');

    function syncVideo() {
        videoFields.style.display = adTypeEl.value === 'video' ? '' : 'none';
    }
    adTypeEl.addEventListener('change', syncVideo);
    syncVideo();

    const radios  = document.querySelectorAll('.action-type-radio');
    const groups  = document.querySelectorAll('.action-fields-group');

    function showFields(selectedType) {
        groups.forEach(g => {
            const types = g.dataset.types ? g.dataset.types.split(',') : [];
            g.style.display = types.includes(selectedType) ? '' : 'none';
        });
    }

    radios.forEach(r => {
        r.addEventListener('change', () => showFields(r.value));
    });

    const checked = document.querySelector('.action-type-radio:checked');
    showFields(checked ? checked.value : '');

    const bgColor  = document.querySelector('[name="background_color"]');
    const bgHex    = document.getElementById('bg_hex');
    const txtColor = document.querySelector('[name="text_color"]');
    const txtHex   = document.getElementById('txt_hex');

    if (bgColor && bgHex) {
        bgColor.addEventListener('input', () => bgHex.value = bgColor.value);
        bgHex.addEventListener('input', () => { if (/^#[0-9a-f]{6}$/i.test(bgHex.value)) bgColor.value = bgHex.value; });
    }
    if (txtColor && txtHex) {
        txtColor.addEventListener('input', () => txtHex.value = txtColor.value);
        txtHex.addEventListener('input', () => { if (/^#[0-9a-f]{6}$/i.test(txtHex.value)) txtColor.value = txtHex.value; });
    }

    // ── الاستهداف الذكي ──────────────────────────────────────
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

    // تهيئة Select2 للبحث مع تنسيق النتائج
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
                    // عرض النتيجة بشكل منسق: الاسم + بريد إلكتروني + هاتف
                    var html = '<div class="d-flex flex-column">';
                    html += '<span class="fw-bold">' + item.name + '</span>';
                    if (item.email) html += '<small class="text-muted">' + item.email + '</small>';
                    if (item.phone) html += '<small class="text-muted">' + item.phone + '</small>';
                    html += '</div>';
                    return $(html);
                },
                templateSelection: function(item) {
                    // عرض العنصر المختار بشكل بسيط
                    return item.name || item.text;
                }
            }).on('select2:select', function(e) {
                const id = e.params.data.id;
                const targetId = $sel.attr('id') === 'customerSelect' ? 'customerId' : 'productId';
                document.getElementById(targetId).value = id;
            });
        });
    }

    // معاينة الجمهور
    document.getElementById('previewTargetBtn').addEventListener('click', function() {
        const payload = {
            target_type: targetTypeEl.value,
            _token: '{{ csrf_token() }}'
        };
        if (payload.target_type === 'customer') payload.customer_id = document.getElementById('customerId').value;
        if (payload.target_type === 'product') payload.product_id = document.getElementById('productId').value;
        if (payload.target_type === 'category') payload.category_id = document.querySelector('[name="target_value[category_id]"]').value;

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
