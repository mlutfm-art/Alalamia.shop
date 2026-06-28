@extends('layouts.admin.app')
@section('title', translate('Predictions_Leaderboard'))
@section('content')
<div class="main-content">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
        <div>
            <h2 class="mb-1 fw-bold fs-4">🏆 {{ translate('Leaderboard') }}</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('admin.predictions.index') }}">{{ translate('Predictions') }}</a></li>
                    <li class="breadcrumb-item active">{{ translate('Leaderboard') }}</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            @foreach(['all'=>translate('All_Time'),'monthly'=>translate('Monthly'),'weekly'=>translate('Weekly'),'daily'=>translate('Daily')] as $key=>$label)
            <a href="?period={{ $key }}" class="btn btn-sm {{ $period===$key?'btn-primary':'btn-outline-secondary' }} px-3">{{ $label }}</a>
            @endforeach
        </div>
    </div>

    @if(count($users)>=3)
    <div class="row g-3 justify-content-center mb-4">
        @foreach([$users[1]??null,$users[0]??null,$users[2]??null] as $i=>$topUser)
        @if($topUser)
        @php $rankIndex=$i===1?0:($i===0?1:2); @endphp
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow text-center {{ $rankIndex===0?'border-top border-4 border-warning':'' }}">
                <div class="card-body py-3">
                    <div class="fs-1">{{ ['🥇','🥈','🥉'][$rankIndex] }}</div>
                    <div class="fw-bold">{{ $topUser->f_name }} {{ $topUser->l_name }}</div>
                    <div class="text-muted small mb-2">{{ $topUser->email }}</div>
                    <div class="badge bg-primary bg-opacity-10 text-primary fs-6 fw-bold px-3 py-2">{{ number_format($topUser->total_points) }} {{ translate('pts') }}</div>
                    <div class="text-muted small mt-1">{{ $topUser->correct_full_count }} {{ translate('exact') }} / {{ $topUser->total_predictions }} {{ translate('total') }}</div>
                </div>
            </div>
        </div>
        @endif
        @endforeach
    </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3 text-center" style="width:60px">{{ translate('Rank') }}</th>
                            <th>{{ translate('User') }}</th>
                            <th class="text-center">{{ translate('Total_Predictions') }}</th>
                            <th class="text-center">{{ translate('Exact') }}</th>
                            <th class="text-center">{{ translate('Total_Points') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr class="{{ $user->rank<=3?'table-warning bg-opacity-25':'' }}">
                            <td class="ps-3 text-center">
                                @if($user->rank===1) 🥇 @elseif($user->rank===2) 🥈 @elseif($user->rank===3) 🥉
                                @else <span class="text-muted fw-bold">#{{ $user->rank }}</span> @endif
                            </td>
                            <td><div class="fw-semibold">{{ $user->f_name }} {{ $user->l_name }}</div><div class="text-muted small">{{ $user->email }}</div></td>
                            <td class="text-center">{{ $user->total_predictions }}</td>
                            <td class="text-center"><span class="badge bg-success bg-opacity-10 text-success fw-semibold">{{ $user->correct_full_count }}</span></td>
                            <td class="text-center"><span class="badge bg-primary bg-opacity-10 text-primary fw-bold fs-6 px-3">{{ number_format($user->total_points) }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center py-5 text-muted"><div class="fs-2 mb-2">🏆</div><div>{{ translate('No_data_for_this_period') }}</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
