@extends('layouts.admin.app')
@section('title', translate('Predictions_Settings'))
@section('content')
<div class="main-content">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
        <div>
            <h2 class="mb-1 fw-bold fs-4">⚙️ {{ translate('Predictions_Settings') }}</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('admin.predictions.index') }}">{{ translate('Predictions') }}</a></li>
                    <li class="breadcrumb-item active">{{ translate('Settings') }}</li>
                </ol>
            </nav>
        </div>
    </div>
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show"><i class="fi fi-rr-check me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    <form method="POST" action="{{ route('admin.predictions.settings.update') }}">
        @csrf
        <div class="row g-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3"><h6 class="fw-bold mb-0">{{ translate('Module_Status') }}</h6></div>
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded">
                            <div>
                                <div class="fw-semibold">{{ translate('Enable_Predictions_Module') }}</div>
                                <div class="text-muted small">{{ translate('When_disabled_users_cannot_submit_predictions') }}</div>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="enabled" value="1" id="enabledToggle" {{ ($settings['enabled']??'1')=='1'?'checked':'' }} style="width:3em;height:1.5em;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3"><h6 class="fw-bold mb-0">🎯 {{ translate('Points_Configuration') }}</h6></div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">{{ translate('Default_Reward_Points') }}</label>
                                <input type="number" name="default_reward_points" class="form-control" min="1" max="100000" required value="{{ $settings['default_reward_points']??100 }}">
                                <div class="form-text">{{ translate('Points_awarded_for_exact_prediction') }}</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">{{ translate('Close_Prediction_Threshold') }}</label>
                                <input type="number" name="close_threshold" class="form-control" min="0" max="20" required value="{{ $settings['close_threshold']??2 }}">
                                <div class="form-text">{{ translate('Max_distance_score_for_partial_reward') }}</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">{{ translate('Partial_Reward_Multiplier') }}</label>
                                <input type="number" name="partial_reward_multiplier" class="form-control" min="0" max="1" step="0.05" required value="{{ $settings['partial_reward_multiplier']??0.5 }}">
                                <div class="form-text">{{ translate('Fraction_of_points') }} (0–1)</div>
                            </div>
                        </div>
                        <div class="alert alert-info mt-3 small">
                            <strong>{{ translate('Scoring_Logic') }}:</strong>
                            distance = |actual1−pred1| + |actual2−pred2|<br>
                            distance=0 → {{ translate('full_points') }} &nbsp;|&nbsp;
                            distance≤threshold → {{ translate('partial_points') }} &nbsp;|&nbsp;
                            distance>threshold → 0
                        </div>
                    </div>
                </div>
            </div>
            {{-- ══════════════════════════════════════════════════════════════
                 APP BANNER — Dynamic banner shown inside the customer app.
                 Tapping the banner navigates to the existing prediction page.
                 ══════════════════════════════════════════════════════════════ --}}
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <h6 class="fw-bold mb-0">📢 {{ translate('App_Banner') }}</h6>
                    </div>
                    <div class="card-body">
                        {{-- Enable Banner --}}
                        <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded mb-3">
                            <div>
                                <div class="fw-semibold">{{ translate('Enable_Banner') }}</div>
                                <div class="text-muted small">{{ translate('Show_promotional_banner_inside_the_app') }}</div>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="banner_enabled" value="1" id="bannerEnabledToggle" {{ ($settings['banner_enabled']??'0')=='1'?'checked':'' }} style="width:3em;height:1.5em;">
                            </div>
                        </div>
                        <div class="row g-3">
                            {{-- Banner Title --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">{{ translate('Banner_Title') }}</label>
                                <input type="text" name="banner_title" class="form-control" maxlength="255" value="{{ $settings['banner_title']??'' }}" placeholder="{{ translate('Enter_banner_title') }}">
                            </div>
                            {{-- Button Text --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">{{ translate('Button_Text') }}</label>
                                <input type="text" name="banner_button_text" class="form-control" maxlength="100" value="{{ $settings['banner_button_text']??'' }}" placeholder="{{ translate('e_g_Predict_Now') }}">
                            </div>
                            {{-- Banner Description --}}
                            <div class="col-12">
                                <label class="form-label fw-semibold small">{{ translate('Banner_Description') }}</label>
                                <textarea name="banner_description" class="form-control" rows="2" maxlength="500" placeholder="{{ translate('Enter_banner_description') }}">{{ $settings['banner_description']??'' }}</textarea>
                            </div>
                            {{-- Banner Image --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">{{ translate('Banner_Image_URL') }}</label>
                                <input type="url" name="banner_image" class="form-control" maxlength="1000" value="{{ $settings['banner_image']??'' }}" placeholder="https://example.com/banner.jpg">
                                <div class="form-text">{{ translate('Direct_URL_to_banner_image') }}</div>
                            </div>
                            {{-- Target Match --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">{{ translate('Target_Match') }}</label>
                                <select name="banner_match_id" class="form-control">
                                    <option value="">{{ translate('Auto_select_nearest_active_match') }}</option>
                                    @foreach($activeMatches as $m)
                                        <option value="{{ $m->id }}" {{ ($settings['banner_match_id']??'')==$m->id?'selected':'' }}>
                                            #{{ $m->id }} — {{ $m->team1_name }} vs {{ $m->team2_name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">{{ translate('Match_to_open_when_banner_is_tapped') }}</div>
                            </div>
                        </div>
                        {{-- Banner preview --}}
                        @if(($settings['banner_image']??'') !== '')
                        <div class="mt-3 p-3 bg-light rounded text-center">
                            <div class="text-muted small mb-2">{{ translate('Banner_Preview') }}</div>
                            <img src="{{ $settings['banner_image'] }}" alt="Banner" class="img-fluid rounded" style="max-height:150px;">
                        </div>
                        @endif
                        <div class="alert alert-info mt-3 small">
                            <strong>{{ translate('How_it_works') }}:</strong>
                            {{ translate('When_enabled_the_banner_is_shown_inside_the_app_Tapping_it_opens_the_existing_prediction_page_for_the_selected_match') }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 d-flex justify-content-end gap-2">
                <a href="{{ route('admin.predictions.index') }}" class="btn btn-outline-secondary px-4">{{ translate('Cancel') }}</a>
                <button type="submit" class="btn btn-primary px-4"><i class="fi fi-rr-disk me-2"></i>{{ translate('Save_Settings') }}</button>
            </div>
        </div>
    </form>
</div>
@endsection
@push('script')
<script>
document.querySelector('form').addEventListener('submit',function(){
    // Module enabled toggle (existing logic — untouched)
    const cb=document.getElementById('enabledToggle');
    if(!cb.checked){const h=document.createElement('input');h.type='hidden';h.name='enabled';h.value='0';this.appendChild(h);cb.removeAttribute('name');}
    // Banner enabled toggle (new — same pattern)
    const bcb=document.getElementById('bannerEnabledToggle');
    if(!bcb.checked){const h2=document.createElement('input');h2.type='hidden';h2.name='banner_enabled';h2.value='0';this.appendChild(h2);bcb.removeAttribute('name');}
});
</script>
@endpush
