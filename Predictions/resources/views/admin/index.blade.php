@extends('layouts.admin.app')

@section('title', translate('Predictions_Dashboard'))

@push('css_or_js')
<style>
.pred-stat-card { border-radius: 12px; transition: transform .15s; }
.pred-stat-card:hover { transform: translateY(-3px); }
.pred-stat-icon { width: 52px; height: 52px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 22px; }
</style>
@endpush

@section('content')
<div class="main-content">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
        <div>
            <h2 class="mb-1 fw-bold fs-4">⚽ {{ translate('Predictions_Dashboard') }}</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard.index') }}">{{ translate('Dashboard') }}</a></li>
                    <li class="breadcrumb-item active">{{ translate('Predictions') }}</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('admin.predictions.matches') }}" class="btn btn-primary btn-sm px-3">
            <i class="fi fi-rr-trophy me-1"></i> {{ translate('Manage_Matches') }}
        </a>
    </div>

    {{-- Stats Cards --}}
    <div class="row g-3 mb-4">
        @php
        $cards = [
            ['label'=>translate('Total_Matches'),     'value'=>$stats['total_matches'],         'icon'=>'🏟️','bg'=>'bg-primary bg-opacity-10','text'=>'text-primary'],
            ['label'=>translate('Active_Matches'),    'value'=>$stats['active_matches'],         'icon'=>'🟢','bg'=>'bg-success bg-opacity-10','text'=>'text-success'],
            ['label'=>translate('Completed_Matches'), 'value'=>$stats['completed_matches'],      'icon'=>'✅','bg'=>'bg-info bg-opacity-10',   'text'=>'text-info'],
            ['label'=>translate('Total_Predictions'), 'value'=>$stats['total_predictions'],      'icon'=>'🎯','bg'=>'bg-warning bg-opacity-10','text'=>'text-warning'],
            ['label'=>translate('Evaluated'),         'value'=>$stats['evaluated_predictions'],  'icon'=>'📊','bg'=>'bg-danger bg-opacity-10', 'text'=>'text-danger'],
            ['label'=>translate('Participants'),      'value'=>$stats['unique_participants'],     'icon'=>'👥','bg'=>'bg-secondary bg-opacity-10','text'=>'text-secondary'],
        ];
        @endphp
        @foreach($cards as $card)
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card pred-stat-card h-100 shadow-sm border-0">
                <div class="card-body text-center p-3">
                    <div class="pred-stat-icon {{ $card['bg'] }} mx-auto mb-2">{{ $card['icon'] }}</div>
                    <div class="fw-bold fs-4 {{ $card['text'] }}">{{ number_format($card['value']) }}</div>
                    <div class="text-muted small">{{ $card['label'] }}</div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="row g-3">
        {{-- Chart --}}
        <div class="col-lg-7">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0 pb-0">
                    <h6 class="fw-bold mb-0">📈 {{ translate('Predictions_Last_6_Months') }}</h6>
                </div>
                <div class="card-body">
                    <div id="predictionsChart"></div>
                </div>
            </div>
        </div>

        {{-- Recent Matches --}}
        <div class="col-lg-5">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0 d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold mb-0">🏆 {{ translate('Recent_Matches') }}</h6>
                    <a href="{{ route('admin.predictions.matches') }}" class="btn btn-sm btn-outline-primary px-2 py-1 small">{{ translate('View_All') }}</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 small">
                            <tbody>
                                @forelse($matches as $match)
                                <tr>
                                    <td class="ps-3 py-2">
                                        <div class="fw-semibold">{{ $match->team1_name }} <span class="text-muted">vs</span> {{ $match->team2_name }}</div>
                                        <div class="text-muted" style="font-size:11px">{{ $match->match_time->format('d M Y H:i') }}</div>
                                    </td>
                                    <td class="text-center py-2">
                                        <span class="badge rounded-pill {{ $match->status==='active' ? 'bg-success' : ($match->status==='completed' ? 'bg-secondary' : 'bg-warning text-dark') }}">
                                            {{ translate($match->status) }}
                                        </span>
                                    </td>
                                    <td class="text-center py-2 text-muted small">{{ $match->predictions_count }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="3" class="text-center text-muted py-4">{{ translate('No_matches_yet') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Links --}}
    <div class="row g-3 mt-1">
        @php
        $links = [
            ['href'=>route('admin.predictions.matches'),    'icon'=>'🏟️','label'=>translate('Matches'),    'desc'=>translate('Manage_all_matches')],
            ['href'=>route('admin.predictions.list'),       'icon'=>'🎯','label'=>translate('Predictions'),'desc'=>translate('View_all_predictions')],
            ['href'=>route('admin.predictions.leaderboard'),'icon'=>'🏆','label'=>translate('Leaderboard'),'desc'=>translate('View_rankings')],
            ['href'=>route('admin.predictions.settings'),   'icon'=>'⚙️','label'=>translate('Settings'),   'desc'=>translate('Module_settings')],
        ];
        @endphp
        @foreach($links as $link)
        <div class="col-6 col-md-3">
            <a href="{{ $link['href'] }}" class="card border-0 shadow-sm text-decoration-none h-100">
                <div class="card-body text-center p-3">
                    <div class="fs-2 mb-1">{{ $link['icon'] }}</div>
                    <div class="fw-semibold text-dark">{{ $link['label'] }}</div>
                    <div class="text-muted small">{{ $link['desc'] }}</div>
                </div>
            </a>
        </div>
        @endforeach
    </div>
</div>
@endsection

@push('script')
<script>
document.addEventListener('DOMContentLoaded',function(){
    new ApexCharts(document.querySelector('#predictionsChart'),{
        chart:{type:'area',height:220,toolbar:{show:false},fontFamily:'inherit'},
        series:[{name:'{{ translate("Predictions") }}',data:{!! json_encode($chart['data']) !!}}],
        xaxis:{categories:{!! json_encode($chart['labels']) !!}},
        colors:['#4f46e5'],
        fill:{type:'gradient',gradient:{shadeIntensity:1,opacityFrom:0.5,opacityTo:0.05}},
        stroke:{curve:'smooth',width:2},
        tooltip:{y:{formatter:v=>v+' {{ translate("predictions") }}'}},
        dataLabels:{enabled:false},
        grid:{borderColor:'#f0f0f0'},
    }).render();
});
</script>
@endpush
