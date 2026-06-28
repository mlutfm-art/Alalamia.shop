@extends('layouts.admin.app')
@section('title', translate('Matches_Management'))
@section('content')
<div class="main-content">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
        <div>
            <h2 class="mb-1 fw-bold fs-4">🏟️ {{ translate('Matches_Management') }}</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('admin.predictions.index') }}">{{ translate('Predictions') }}</a></li>
                    <li class="breadcrumb-item active">{{ translate('Matches') }}</li>
                </ol>
            </nav>
        </div>
        <button type="button" class="btn btn-primary btn-sm px-3" data-bs-toggle="modal" data-bs-target="#addMatchModal">
            <i class="fi fi-rr-plus me-1"></i> {{ translate('Add_Match') }}
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show"><i class="fi fi-rr-check me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show"><i class="fi fi-rr-cross-circle me-2"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-5"><input type="text" name="search" class="form-control form-control-sm" placeholder="{{ translate('Search_by_team_name') }}" value="{{ $search??'' }}"></div>
                <div class="col-md-3">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">{{ translate('All_Statuses') }}</option>
                        <option value="active"    {{ ($status??'')==='active'    ?'selected':'' }}>{{ translate('Active') }}</option>
                        <option value="closed"    {{ ($status??'')==='closed'    ?'selected':'' }}>{{ translate('Closed') }}</option>
                        <option value="completed" {{ ($status??'')==='completed' ?'selected':'' }}>{{ translate('Completed') }}</option>
                    </select>
                </div>
                <div class="col-md-2"><button class="btn btn-primary btn-sm w-100">{{ translate('Filter') }}</button></div>
                <div class="col-md-2"><a href="{{ route('admin.predictions.matches') }}" class="btn btn-outline-secondary btn-sm w-100">{{ translate('Reset') }}</a></div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">#</th>
                            <th>{{ translate('Match') }}</th>
                            <th>{{ translate('Match_Time') }}</th>
                            <th>{{ translate('Close_Time') }}</th>
                            <th class="text-center">{{ translate('Status') }}</th>
                            <th class="text-center">{{ translate('Predictions') }}</th>
                            <th class="text-center">{{ translate('Points') }}</th>
                            <th class="text-center">{{ translate('Result') }}</th>
                            <th class="text-center">{{ translate('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($matches as $match)
                        <tr>
                            <td class="ps-3 text-muted small">{{ $match->id }}</td>
                            <td>
                                <div class="fw-semibold small">{{ $match->team1_name }} <span class="text-muted">vs</span> {{ $match->team2_name }}</div>
                                @if($match->title)<div class="text-muted" style="font-size:11px">{{ $match->title }}</div>@endif
                            </td>
                            <td class="small">{{ $match->match_time->format('d M Y') }}<br><span class="text-muted">{{ $match->match_time->format('H:i') }}</span></td>
                            <td class="small">{{ $match->prediction_close_time->format('d M Y') }}<br><span class="text-muted">{{ $match->prediction_close_time->format('H:i') }}</span></td>
                            <td class="text-center">
                                <span class="badge rounded-pill {{ $match->status==='active'?'bg-success':($match->status==='completed'?'bg-secondary':'bg-warning text-dark') }}">
                                    {{ translate($match->status) }}
                                </span>
                            </td>
                            <td class="text-center fw-semibold">{{ $match->predictions_count }}</td>
                            <td class="text-center"><span class="badge bg-primary bg-opacity-10 text-primary">{{ $match->reward_points }}</span></td>
                            <td class="text-center">
                                @if($match->status==='completed')
                                    <span class="fw-bold text-success small">{{ $match->actual_team1 }} - {{ $match->actual_team2 }}</span>
                                @elseif($match->status==='active')
                                    <button class="btn btn-sm btn-outline-success py-0 px-2" data-bs-toggle="modal" data-bs-target="#resultModal{{ $match->id }}">
                                        {{ translate('Enter_Result') }}
                                    </button>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    {{-- Edit --}}
                                    <button class="btn btn-sm btn-outline-primary py-0 px-2" title="{{ translate('Edit') }}"
                                        data-bs-toggle="modal" data-bs-target="#editMatchModal{{ $match->id }}">
                                        <i class="fi fi-rr-pencil"></i>
                                    </button>

                                    {{-- 🔔 Send Notification (active matches only) --}}
                                    @if($match->status === 'active')
                                    <form method="POST" action="{{ route('admin.predictions.matches.notify', $match->id) }}"
                                          onsubmit="return confirm('{{ translate('Send_notification_to_all_customers') }}?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-warning py-0 px-2"
                                                title="{{ translate('Notify_Customers') }}">
                                            🔔
                                        </button>
                                    </form>
                                    @endif

                                    {{-- Delete --}}
                                    <form method="POST" action="{{ route('admin.predictions.matches.destroy',$match->id) }}"
                                          onsubmit="return confirm('{{ translate('confirm_delete') }}')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger py-0 px-2" type="submit" title="{{ translate('Delete') }}">
                                            <i class="fi fi-rr-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        {{-- Edit Modal --}}
                        <div class="modal fade" id="editMatchModal{{ $match->id }}" tabindex="-1">
                            <div class="modal-dialog modal-lg"><div class="modal-content">
                                <form method="POST" action="{{ route('admin.predictions.matches.update',$match->id) }}">
                                    @csrf @method('PUT')
                                    <div class="modal-header">
                                        <h5 class="modal-title">✏️ {{ translate('Edit_Match') }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">@include('predictions::admin.partials.match-form',['match'=>$match])</div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ translate('Cancel') }}</button>
                                        <button type="submit" class="btn btn-primary">{{ translate('Update') }}</button>
                                    </div>
                                </form>
                            </div></div>
                        </div>

                        {{-- Result Modal --}}
                        @if($match->status==='active')
                        <div class="modal fade" id="resultModal{{ $match->id }}" tabindex="-1">
                            <div class="modal-dialog"><div class="modal-content">
                                <form method="POST" action="{{ route('admin.predictions.matches.result',$match->id) }}">
                                    @csrf
                                    <div class="modal-header">
                                        <h5 class="modal-title">✅ {{ translate('Enter_Match_Result') }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p class="fw-semibold">{{ $match->team1_name }} vs {{ $match->team2_name }}</p>
                                        <div class="row g-3">
                                            <div class="col-6">
                                                <label class="form-label fw-semibold">{{ $match->team1_name }}</label>
                                                <input type="number" name="actual_team1" class="form-control" min="0" max="99" required>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label fw-semibold">{{ $match->team2_name }}</label>
                                                <input type="number" name="actual_team2" class="form-control" min="0" max="99" required>
                                            </div>
                                        </div>
                                        <div class="alert alert-warning mt-3 small">
                                            <i class="fi fi-rr-info me-1"></i>{{ translate('This_will_evaluate_all_predictions_and_award_points') }}
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ translate('Cancel') }}</button>
                                        <button type="submit" class="btn btn-success">{{ translate('Submit_Result') }}</button>
                                    </div>
                                </form>
                            </div></div>
                        </div>
                        @endif

                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <div class="fs-2 mb-2">🏟️</div>
                                <div>{{ translate('No_matches_found') }}</div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($matches->hasPages())
                <div class="d-flex justify-content-center py-3">{{ $matches->links() }}</div>
            @endif
        </div>
    </div>
</div>

{{-- Add Match Modal --}}
<div class="modal fade" id="addMatchModal" tabindex="-1">
    <div class="modal-dialog modal-lg"><div class="modal-content">
        <form method="POST" action="{{ route('admin.predictions.matches.store') }}">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">➕ {{ translate('Add_New_Match') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">@include('predictions::admin.partials.match-form',['match'=>null])</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ translate('Cancel') }}</button>
                <button type="submit" class="btn btn-primary">{{ translate('Add_Match') }}</button>
            </div>
        </form>
    </div></div>
</div>
@endsection
