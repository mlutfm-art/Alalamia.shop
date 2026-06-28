@extends('layouts.admin.app')
@section('title', translate('Predictions_List'))
@section('content')
<div class="main-content">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
        <div>
            <h2 class="mb-1 fw-bold fs-4">🎯 {{ translate('Predictions_List') }}</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('admin.predictions.index') }}">{{ translate('Predictions') }}</a></li>
                    <li class="breadcrumb-item active">{{ translate('All_Predictions') }}</li>
                </ol>
            </nav>
        </div>
    </div>
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-5"><input type="text" name="search" class="form-control form-control-sm" placeholder="{{ translate('Search_by_user') }}" value="{{ $search??'' }}"></div>
                <div class="col-md-3">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">{{ translate('All_Statuses') }}</option>
                        <option value="pending"   {{ ($status??'')==='pending'   ?'selected':'' }}>{{ translate('Pending') }}</option>
                        <option value="evaluated" {{ ($status??'')==='evaluated' ?'selected':'' }}>{{ translate('Evaluated') }}</option>
                    </select>
                </div>
                <div class="col-md-2"><button class="btn btn-primary btn-sm w-100">{{ translate('Filter') }}</button></div>
                <div class="col-md-2"><a href="{{ route('admin.predictions.list') }}" class="btn btn-outline-secondary btn-sm w-100">{{ translate('Reset') }}</a></div>
            </form>
        </div>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">#</th><th>{{ translate('User') }}</th><th>{{ translate('Match') }}</th>
                            <th class="text-center">{{ translate('Prediction') }}</th><th class="text-center">{{ translate('Actual') }}</th>
                            <th class="text-center">{{ translate('Distance') }}</th><th class="text-center">{{ translate('Points') }}</th>
                            <th class="text-center">{{ translate('Status') }}</th><th class="text-end pe-3">{{ translate('Date') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($predictions as $p)
                        <tr>
                            <td class="ps-3 text-muted small">{{ $p->id }}</td>
                            <td><div class="fw-semibold small">{{ $p->user->f_name??'—' }} {{ $p->user->l_name??'' }}</div><div class="text-muted" style="font-size:11px">{{ $p->user->email??'—' }}</div></td>
                            <td class="small fw-semibold">{{ $p->match_details->team1_name??'—' }} vs {{ $p->match_details->team2_name??'—' }}</td>
                            <td class="text-center"><span class="badge bg-primary bg-opacity-10 text-primary fw-bold">{{ $p->predicted_team1 }} – {{ $p->predicted_team2 }}</span></td>
                            <td class="text-center">
                                @if($p->match_details && $p->match_details->status==='completed')
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary fw-bold">{{ $p->match_details->actual_team1 }} – {{ $p->match_details->actual_team2 }}</span>
                                @else <span class="text-muted">—</span> @endif
                            </td>
                            <td class="text-center">
                                @if($p->prediction_status==='evaluated')
                                    <span class="badge {{ $p->distance_score==0?'bg-success':($p->distance_score<=2?'bg-warning text-dark':'bg-danger') }}">{{ $p->distance_score }}</span>
                                @else <span class="text-muted">—</span> @endif
                            </td>
                            <td class="text-center">
                                @if($p->prediction_status==='evaluated')
                                    <span class="fw-bold {{ $p->points_awarded>0?'text-success':'text-muted' }}">{{ number_format($p->points_awarded??0) }}</span>
                                @else <span class="text-muted">—</span> @endif
                            </td>
                            <td class="text-center"><span class="badge rounded-pill {{ $p->prediction_status==='evaluated'?'bg-success':'bg-warning text-dark' }}">{{ translate($p->prediction_status) }}</span></td>
                            <td class="text-end pe-3 text-muted small">{{ $p->created_at->format('d M Y') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="9" class="text-center py-5 text-muted"><div class="fs-2 mb-2">🎯</div><div>{{ translate('No_predictions_found') }}</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($predictions->hasPages())<div class="d-flex justify-content-center py-3">{{ $predictions->links() }}</div>@endif
        </div>
    </div>
</div>
@endsection
