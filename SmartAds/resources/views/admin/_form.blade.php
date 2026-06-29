@php
    $ad  = $ad  ?? null;
    $ac  = $ad?->action_data ?? [];
    $currentActionType = old('action_type', $ad->action_type ?? ($ac['type'] ?? 'product'));
    $firebase = $ad?->firebase_payload ?? [];
    $targeting = $ad?->targeting_config ?? [];
@endphp

<div class="row g-3 text-right" dir="rtl" id="enterprise-action-engine-container">
    {{-- الجانب الأيمن: المحتوى والذكاء --}}
    <div class="col-lg-8">
        {{-- 1. المحتوى المرئي والتخصيص --}}
        <div class="card mb-3 shadow-sm border-right-primary">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-primary"><i class="tio-edit mr-2"></i>المحتوى والتخصيص المتقدم</h5>
                @if($ad?->is_ai_generated) <span class="badge badge-soft-info">ذكاء اصطناعي</span> @endif
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label fw-bold">العنوان الديناميكي <small class="text-info">(استخدم @{{user_name}} لاسم العميل)</small></label>
                        <input type="text" name="title" class="form-control form-control-lg text-right" value="{{ old('title', $ad->title ?? '') }}" required placeholder="مثال: مرحباً @{{user_name}}، عرض خاص لك!">
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="form-label fw-bold">الوصف / نص الإشعار</label>
                        <textarea name="sub_title" class="form-control text-right" rows="3" placeholder="اكتب تفاصيل العرض هنا...">{{ old('sub_title', $ad->sub_title ?? '') }}</textarea>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">نوع الإعلان (الظهور)</label>
                        <select name="ad_type" id="ad_type" class="form-control select2">
                            <option value="banner" @selected($ad?->ad_type == 'banner')>بنر داخلي</option>
                            <option value="popup" @selected($ad?->ad_type == 'popup')>نافذة منبثقة</option>
                            <option value="notification" @selected($ad?->ad_type == 'notification')>إشعار خارجي (Push)</option>
                            <option value="carousel" @selected($ad?->ad_type == 'carousel')>قصص (Stories)</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">مكان الظهور</label>
                        <input type="text" name="placement" class="form-control text-right" value="{{ $ad->placement ?? 'home' }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">نص الزر</label>
                        <input type="text" name="button_text" class="form-control text-right" value="{{ $ad->button_text ?? 'عرض الآن' }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. محرك الإجراءات الذكي (Action Engine) --}}
        <div class="card mb-3 shadow-sm border-right-warning">
            <div class="card-header bg-warning-light">
                <h5 class="mb-0 text-warning"><i class="tio-flash-outlined mr-2"></i>محرك الإجراءات (Action Engine)</h5>
            </div>
            <div class="card-body">
                {{-- أزرار التبويبات الرئيسية --}}
                <ul class="nav nav-pills mb-4 enterprise-tabs" id="action-tabs-list" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="pill" data-bs-toggle="pill" href="#cat-nav" role="tab">🧭 التنقل</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="pill" data-bs-toggle="pill" href="#cat-gamify" role="tab">🎁 الألعاب والجوائز</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="pill" data-bs-toggle="pill" href="#cat-social" role="tab">👥 التواصل الاجتماعي</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="pill" data-bs-toggle="pill" href="#cat-comm" role="tab">📱 الاتصال المباشر</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="pill" data-bs-toggle="pill" href="#cat-advanced" role="tab">🚀 خيارات متقدمة</a>
                    </li>
                </ul>

                <div class="tab-content p-3 border rounded bg-white shadow-none text-right">
                    {{-- التنقل --}}
                    <div class="tab-pane fade show active" id="cat-nav" role="tabpanel">
                        <div class="row g-2">
                            @foreach(['product' => 'تفاصيل المنتج', 'category' => 'قائمة التصنيفات', 'brand' => 'صفحة الماركة', 'flash_deals' => 'عروض الفلاش', 'wallet' => 'محفظتي', 'order_tracking' => 'تتبع الطلب'] as $val => $lbl)
                                <div class="col-md-4 mb-2">
                                    <div class="action-selector {{ $currentActionType == $val ? 'active' : '' }}" data-type="{{ $val }}" onclick="setEnterpriseAction('{{ $val }}', this)">{{ $lbl }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- الألعاب --}}
                    <div class="tab-pane fade" id="cat-gamify" role="tabpanel">
                        <div class="row g-2">
                            @foreach(['scratch_card' => 'امسح واربح', 'spin_wheel' => 'عجلة الحظ', 'countdown' => 'عداد الاستعجال'] as $val => $lbl)
                                <div class="col-md-4 mb-2">
                                    <div class="action-selector {{ $currentActionType == $val ? 'active' : '' }}" data-type="{{ $val }}" onclick="setEnterpriseAction('{{ $val }}', this)">{{ $lbl }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- التواصل الاجتماعي --}}
                    <div class="tab-pane fade" id="cat-social" role="tabpanel">
                         <div class="row g-2">
                            @foreach(['facebook_follow' => 'متابعة فيسبوك', 'instagram_follow' => 'متابعة إنستغرام', 'tiktok_follow' => 'متابعة تيك توك', 'youtube_subscribe' => 'اشتراك يوتيوب', 'telegram_join' => 'انضمام تيليجرام'] as $val => $lbl)
                                <div class="col-md-4 mb-2">
                                    <div class="action-selector {{ $currentActionType == $val ? 'active' : '' }}" data-type="{{ $val }}" onclick="setEnterpriseAction('{{ $val }}', this)">{{ $lbl }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- الاتصال --}}
                    <div class="tab-pane fade" id="cat-comm" role="tabpanel">
                        <div class="row g-2">
                            @foreach(['whatsapp_chat' => 'محادثة واتساب', 'call_phone' => 'اتصال هاتفي', 'save_contact' => 'حفظ جهة الاتصال'] as $val => $lbl)
                                <div class="col-md-4 mb-2">
                                    <div class="action-selector {{ $currentActionType == $val ? 'active' : '' }}" data-type="{{ $val }}" onclick="setEnterpriseAction('{{ $val }}', this)">{{ $lbl }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- متقدم --}}
                    <div class="tab-pane fade" id="cat-advanced" role="tabpanel">
                         <div class="row g-2">
                            @foreach(['external_url' => 'فتح رابط خارجي', 'apply_coupon' => 'تطبيق كوبون تلقائي', 'copy_to_clipboard' => 'نسخ نص للحافظة', 'survey' => 'استبيان سريع'] as $val => $lbl)
                                <div class="col-md-4 mb-2">
                                    <div class="action-selector {{ $currentActionType == $val ? 'active' : '' }}" data-type="{{ $val }}" onclick="setEnterpriseAction('{{ $val }}', this)">{{ $lbl }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <input type="hidden" name="action_type" id="final_action_type" value="{{ $currentActionType }}">

                {{-- الحقول الديناميكية --}}
                <div id="dynamic-action-fields" class="mt-4 p-3 bg-light border-dashed rounded text-right">
                    {{-- المنتج --}}
                    <div class="action-field-group" id="field-product" style="display:none">
                        <label class="form-label fw-bold">اختر المنتج المستهدف</label>
                        <select name="product_id" class="form-control select2-ajax-products">
                            @if($ad && isset($ac['id']) && $currentActionType == 'product')
                                <option value="{{ $ac['id'] }}" selected>{{ $ad->product?->name ?? 'منتج مختار' }}</option>
                            @endif
                        </select>
                    </div>

                    {{-- القسم --}}
                    <div class="action-field-group" id="field-category" style="display:none">
                        <label class="form-label fw-bold">اختر القسم المستهدف</label>
                        <select name="category_id" class="form-control select2">
                            @foreach(\App\Models\Category::where('position', 0)->get() as $category)
                                <option value="{{ $category->id }}" @selected(isset($ac['id']) && $ac['id'] == $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- الهاتف والاتصال --}}
                    <div class="action-field-group" id="field-whatsapp" style="display:none">
                        <label class="form-label fw-bold">رقم الهاتف (مع كود الدولة مثل +966)</label>
                        <input type="text" name="wa_phone" class="form-control text-right" value="{{ $ac['phone'] ?? '' }}" placeholder="+966...">
                    </div>

                    {{-- الروابط والمعرفات --}}
                    <div class="action-field-group" id="field-url" style="display:none">
                        <label class="form-label fw-bold">الرابط أو معرف الحساب (URL / Username)</label>
                        <input type="text" name="external_url" class="form-control text-right" value="{{ $ac['url'] ?? $ac['id'] ?? '' }}" placeholder="https://... أو اسم الحساب">
                    </div>

                    {{-- الكوبونات والجوائز --}}
                    <div class="action-field-group" id="field-coupon" style="display:none">
                        <label class="form-label fw-bold">كود الكوبون أو قيمة الجائزة</label>
                        <input type="text" name="coupon_code" class="form-control text-uppercase text-right" value="{{ $ac['coupon'] ?? $ac['countdown'] ?? '' }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- 3. الاستهداف المتقدم --}}
        <div class="card mb-3 shadow-sm border-right-success">
            <div class="card-header bg-success-light text-right">
                <h5 class="mb-0 text-success"><i class="tio-filter-list mr-2"></i>الاستهداف الذكي (الجمهور)</h5>
            </div>
            <div class="card-body text-right">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">شريحة الجمهور المستهدفة</label>
                        <select name="targeting_config[segment]" class="form-control">
                            <option value="all" @selected(($targeting['segment'] ?? '') == 'all')>جميع المستخدمين</option>
                            <option value="abandoned_cart" @selected(($targeting['segment'] ?? '') == 'abandoned_cart')>سلات مهملة</option>
                            <option value="vip_customers" @selected(($targeting['segment'] ?? '') == 'vip_customers')>العملاء المميزون (VIP)</option>
                            <option value="inactive_30d" @selected(($targeting['segment'] ?? '') == 'inactive_30d')>غير نشط منذ 30 يوم</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">الاستهداف الجغرافي (الدول)</label>
                        <input type="text" name="targeting_config[geo]" class="form-control text-right" placeholder="SA, AE, EG" value="{{ $targeting['geo'] ?? '' }}">
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- الجانب الأيسر --}}
    <div class="col-lg-4">
        <div class="card mb-3 shadow-sm border-right-firebase">
            <div class="card-header bg-firebase-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-firebase">إعدادات Firebase V1</h5>
            </div>
            <div class="card-body text-right">
                <div class="mb-3">
                    <label class="form-label">الأولوية</label>
                    <select name="firebase_payload[priority]" class="form-control">
                        <option value="high" @selected(($firebase['priority'] ?? '') == 'high')>عالية (إرسال فوري)</option>
                        <option value="normal" @selected(($firebase['priority'] ?? '') == 'normal')>عادية</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">صوت الإشعار</label>
                    <select name="firebase_payload[sound]" class="form-control">
                        <option value="default" @selected(($firebase['sound'] ?? '') == 'default')>الافتراضي</option>
                        <option value="sale_shout.mp3" @selected(($firebase['sound'] ?? '') == 'sale_shout.mp3')>صوت التخفيضات</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">معرف القناة</label>
                    <input type="text" name="firebase_payload[channel_id]" class="form-control text-right" value="{{ $firebase['channel_id'] ?? 'marketing_channel' }}">
                </div>
            </div>
        </div>

        <div class="card mb-3 shadow-sm border-right-info">
            <div class="card-header bg-info-light text-right">
                <h5 class="mb-0 text-info">الجدولة الزمنية</h5>
            </div>
            <div class="card-body text-right">
                <div class="mb-3">
                    <label class="form-label">تاريخ البدء</label>
                    <input type="datetime-local" name="start_at" class="form-control" value="{{ $ad && $ad->start_at ? $ad->start_at->format('Y-m-d\TH:i') : '' }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">تاريخ الانتهاء</label>
                    <input type="datetime-local" name="end_at" class="form-control" value="{{ $ad && $ad->end_at ? $ad->end_at->format('Y-m-d\TH:i') : '' }}">
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header text-right">الوسائط</div>
            <div class="card-body text-center">
                @if($ad?->image_url)
                    <img src="{{ $ad->image_url }}" class="img-fluid rounded border mb-3" style="max-height: 120px">
                @else
                    <div class="p-4 border rounded mb-3 bg-light"><i class="tio-image fa-3x text-muted"></i></div>
                @endif
                <input type="file" name="image" class="form-control" accept="image/*">
                <div class="mt-4 text-right">
                    <label class="form-label">لون التنسيق</label>
                    <input type="color" name="background_color" class="form-control w-100" value="{{ $ac['background_color'] ?? '#377dff' }}">
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .text-right { text-align: right !important; }
    .border-right-primary { border-right: 5px solid #377dff !important; }
    .border-right-warning { border-right: 5px solid #ffca28 !important; }
    .border-right-success { border-right: 5px solid #00c9db !important; }
    .border-right-info { border-right: 5px solid #00c9db !important; }
    .border-right-firebase { border-right: 5px solid #f6820c !important; }
    .enterprise-tabs .nav-link { font-size: 13px; font-weight: bold; background: #eee; margin-left: 5px; color: #555; border-radius: 8px; border: 0; }
    .enterprise-tabs .nav-link.active { background: #377dff !important; color: white !important; }
    .action-selector { padding: 12px; border: 1px solid #ddd; border-radius: 8px; text-align: center; cursor: pointer; transition: 0.2s; background: white; font-weight: bold; font-size: 13px; }
    .action-selector:hover { border-color: #377dff; background: #f0f7ff; }
    .action-selector.active { background: #377dff; color: white; border-color: #377dff; box-shadow: 0 4px 10px rgba(55, 125, 255, 0.3); }
    .border-dashed { border: 2px dashed #eee; }
</style>

<script>
    function setEnterpriseAction(type, el) {
        $('.action-selector').removeClass('active');
        if(el) {
            $(el).addClass('active');
        } else {
            $(`.action-selector[data-type="${type}"]`).addClass('active');
        }

        $('#final_action_type').val(type);
        $('.action-field-group').hide();
        
        if (type === 'product') {
            $('#field-product').fadeIn();
        } else if (type === 'category') {
            $('#field-category').fadeIn();
        } else if (['whatsapp_chat', 'call_phone'].includes(type)) {
            $('#field-whatsapp').fadeIn();
        } else if (type.includes('follow') || type.includes('subscribe') || type.includes('join')) {
            $('#field-url').fadeIn();
        } else if (['external_url', 'survey', 'copy_to_clipboard', 'inapp_browser'].includes(type)) {
            $('#field-url').fadeIn();
        } else if (['scratch_card', 'spin_wheel', 'apply_coupon', 'countdown'].includes(type)) {
            $('#field-coupon').fadeIn();
        }
    }

    $(document).ready(function() {
        const initialType = $('#final_action_type').val();
        if(initialType) {
            setEnterpriseAction(initialType);
        }
        
        if($('.select2-ajax-products').length > 0) {
            $('.select2-ajax-products').select2({
                ajax: {
                    url: '{{ route("admin.smartads.search-products") }}',
                    data: function (params) { return { q: params.term }; },
                    processResults: function (data) { return { results: data.results }; }
                }
            });
        }
        
        $('#action-tabs-list a').on('click', function (e) {
          e.preventDefault();
          $(this).tab('show');
        });
    });
</script>
