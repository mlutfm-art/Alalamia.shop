@extends('layouts.admin.app')

@section('title', 'تحليلات A/B — ' . ($ad->title ?? ''))

@push('css_or_js')
<style>
.winner-row {
    background: linear-gradient(90deg, #e8f5e9 0%, #fff 100%);
}

.ctr-bar {
    height: 8px;
    border-radius: 4px;
    background: #e9ecef;
    overflow: hidden;
}

.ctr-fill {
    height: 100%;
    border-radius: 4px;
    background: linear-gradient(90deg, #4caf50, #8bc34a);
    transition: width .6s ease;
}
</style>
@endpush

@section('content')
<div class="content container-fluid">

    {{-- Header --}}
    <div class="page-header mb-4">
        <div class="d-flex align-items-center gap-3">

            {{-- BACK SAFE ROUTE --}}
            <a href="{{ route('admin.smartads.index') ?? url()->previous() }}"
               class="btn btn-outline-secondary btn-sm">
                <i class="tio-arrow-backward"></i>
            </a>

            <div>
                <h1 class="page-header-title mb-0">
                    <i class="tio-chart-bar-3 mr-2 text-info"></i>
                    تحليلات A/B — {{ $ad->title ?? '' }}
                </h1>
                <small class="text-muted">مقارنة أداء المتغيرات التجريبية</small>
            </div>
        </div>
    </div>

    {{-- Winner --}}
    @if(!empty($winner))
        <div class="alert d-flex align-items-center gap-3 mb-4"
             style="background:linear-gradient(135deg,#e8f5e9,#f1f8e9);border:1px solid #a5d6a7;border-radius:12px">

            <span style="font-size:40px">🏆</span>

            <div>
                <div class="fw-bold" style="font-size:17px">
                    الفائز: {{ $winner->title ?? '' }}
                </div>

                <div class="text-muted small">
                    Variant:
                    <strong>{{ $winner->ab_variant ?? 'Main' }}</strong> —
                    CTR:
                    <strong class="text-success">{{ $winner->ctr ?? 0 }}%</strong> —
                    {{ $winner->clicks ?? 0 }} ضغطة من {{ $winner->impressions ?? 0 }} ظهور
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-info mb-4">
            <i class="tio-info-outined mr-2"></i>
            لا توجد بيانات كافية لتحديد الفائز.
        </div>
    @endif

    {{-- TABLE --}}
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0">مقارنة المتغيرات</h6>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">

                <thead class="thead-light">
                <tr>
                    <th>المتغير</th>
                    <th>العنوان</th>
                    <th class="text-center">الظهور</th>
                    <th class="text-center">الضغطات</th>
                    <th style="width:220px">CTR%</th>
                    <th class="text-center">الحالة</th>
                </tr>
                </thead>

                <tbody>
                @php
                    $allVariants = collect([$ad])->merge($ad->variants ?? []);
                    $maxCtr = $allVariants->max('ctr') ?: 1;
                @endphp

                @foreach($allVariants as $row)
                    <tr @class(['winner-row' => !empty($winner) && $winner->id == $row->id])>

                        <td>
                            @if(!empty($winner) && $winner->id == $row->id)
                                <span class="badge badge-success">🏆 فائز</span>
                            @else
                                <span class="badge badge-soft-secondary">
                                    {{ $row->ab_variant ?? 'Main' }}
                                </span>
                            @endif
                        </td>

                        <td>
                            <div class="fw-semibold">{{ $row->title ?? '' }}</div>
                            <small class="text-muted">
                                #{{ $row->id }}
                                @if(!empty($row->image_url))
                                    · <a href="{{ $row->image_url }}" target="_blank">صورة</a>
                                @endif
                            </small>
                        </td>

                        <td class="text-center">{{ number_format($row->impressions ?? 0) }}</td>
                        <td class="text-center">{{ number_format($row->clicks ?? 0) }}</td>

                        <td>
                            <div class="d-flex align-items-center gap-2">

                                <div class="ctr-bar flex-grow-1">
                                    <div class="ctr-fill"
                                         style="width: {{ $maxCtr > 0 ? (($row->ctr ?? 0) / $maxCtr * 100) : 0 }}%">
                                    </div>
                                </div>

                                <span class="fw-bold small">
                                    {{ $row->ctr ?? 0 }}%
                                </span>

                            </div>
                        </td>

                        <td class="text-center">
                            <span class="badge {{ !empty($row->status) ? 'badge-soft-success' : 'badge-soft-danger' }}">
                                {{ !empty($row->status) ? 'نشط' : 'متوقف' }}
                            </span>
                        </td>

                    </tr>
                @endforeach
                </tbody>

            </table>
        </div>
    </div>

    {{-- ACTIONS --}}
    <div class="mt-3">

        <a href="{{ route('admin.smartads.edit', $ad->id ?? 0) }}"
           class="btn btn-outline-primary btn-sm">
            <i class="tio-edit mr-1"></i> تعديل الإعلان
        </a>

        <a href="{{ route('admin.smartads.index') ?? url()->previous() }}"
           class="btn btn-outline-secondary btn-sm mr-2">
            عودة للقائمة
        </a>

    </div>

</div>
@endsection