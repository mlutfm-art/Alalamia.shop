@extends('layouts.admin.app')
@section('title', 'تفاصيل حملة تذكير الجرعات')

@section('content')
<div class="content container-fluid">
    <div class="page-header mb-4">
        <h1 class="page-header-title"><i class="tio-medicine-outlined mr-2"></i>{{ $reminder->name }}</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.smartads.index') }}">SmartAds</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.smartads.dose-reminders') }}">تذكير الجرعات</a></li>
                <li class="breadcrumb-item active">{{ $reminder->name }}</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header"><h5>معلومات الحملة</h5></div>
                <div class="card-body">
                    <p><strong>المنتج:</strong> {{ $reminder->product?->name ?? '—' }}</p>
                    <p><strong>الجرعات:</strong> {{ $reminder->doses_count }}</p>
                    <p><strong>أيام بين الجرعات:</strong> {{ $reminder->days_between_doses }}</p>
                    <p><strong>تذكيرات/جرعة:</strong> {{ $reminder->reminders_per_dose }}</p>
                    <p><strong>الحالة:</strong> <span class="badge {{ $reminder->is_active ? 'badge-soft-success' : 'badge-soft-secondary' }}">{{ $reminder->is_active ? 'نشط' : 'متوقف' }}</span></p>
                    <hr>
                    <p><strong>عنوان الإشعار:</strong> {{ $reminder->notification_title }}</p>
                    <p><strong>نص الإشعار:</strong> {{ $reminder->notification_body }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card mb-3">
                <div class="card-header"><h5>المستلمون</h5></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead><tr><th>المستخدم</th><th>الجرعة الحالية</th><th>التذكيرات المرسلة</th><th>الحالة</th></tr></thead>
                            <tbody>
                                @forelse($recipients as $rec)
                                <tr>
                                    <td>{{ $rec->user?->f_name }} {{ $rec->user?->l_name }}</td>
                                    <td>{{ $rec->current_dose }}/{{ $reminder->doses_count }}</td>
                                    <td>{{ $rec->total_sent }}</td>
                                    <td>{{ $rec->status }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-muted">لا يوجد مستلمون بعد.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $recipients->appends(['logs_page' => request('logs_page')])->links() }}
                </div>
            </div>
            <div class="card">
                <div class="card-header"><h5>سجل الإرسال</h5></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead><tr><th>المستخدم</th><th>الجرعة</th><th>التذكير</th><th>الحالة</th><th>التاريخ</th></tr></thead>
                            <tbody>
                                @forelse($logs as $log)
                                <tr>
                                    <td>{{ $log->recipient?->user?->f_name ?? '—' }}</td>
                                    <td>{{ $log->dose_number }}</td>
                                    <td>{{ $log->reminder_number }}</td>
                                    <td><span class="badge {{ $log->status == 'sent' ? 'badge-soft-success' : 'badge-soft-danger' }}">{{ $log->status }}</span></td>
                                    <td>{{ $log->sent_at?->format('Y-m-d H:i') }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="text-muted">لا يوجد سجل بعد.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $logs->appends(['recipients_page' => request('recipients_page')])->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
