@extends('layouts.admin.app')
@section('title', 'مركز الإشعارات')

@push('css_or_js')
<style>
.notif-card { transition: box-shadow .2s; border-radius: 12px; }
.notif-card:hover { box-shadow: 0 4px 18px rgba(0,0,0,.1); }
.notif-unread { border-right: 4px solid #2196F3; }
.notif-read   { opacity: .75; }
.action-badge { font-size:11px; padding: 3px 9px; border-radius: 20px; }
</style>
@endpush

@section('content')
<div class="content container-fluid">
    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <h1 class="page-header-title mb-0">
                <i class="tio-notifications mr-2 text-primary"></i>
                مركز الإشعارات
            </h1>
            <small class="text-muted">
                {{ $notifications->total() }} إشعار —
                {{ $unreadCount }} غير مقروء
            </small>
        </div>
        <div class="d-flex gap-2">
            <button id="read-all-btn" class="btn btn-outline-secondary btn-sm">
                <i class="tio-checkmark-circle-outlined mr-1"></i> قراءة الكل
            </button>
            <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#fcmModal"><i class="tio-notifications mr-1"></i> إرسال Push</button>
            <a href="{{ route('admin.smartads.create') }}" class="btn btn-primary btn-sm">
                <i class="tio-add mr-1"></i> إعلان جديد
            </a>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card mb-3">
        <div class="card-body py-2">
            <form method="GET" action="{{ route('admin.smartads.notifications') }}"
                  class="d-flex flex-wrap gap-2 align-items-center">
                <select name="display_type" class="form-control form-control-sm" style="width:180px">
                    <option value="">كل الأنواع</option>
                    <option value="push" {{ request('display_type')=='push'?'selected':'' }}>Push</option>
                    <option value="inapp_banner" {{ request('display_type')=='inapp_banner'?'selected':'' }}>In-App Banner</option>
                    <option value="notification_center" {{ request('display_type')=='notification_center'?'selected':'' }}>Notification Center</option>
                </select>
                <select name="unread_only" class="form-control form-control-sm" style="width:150px">
                    <option value="">الكل</option>
                    <option value="1" {{ request('unread_only')=='1'?'selected':'' }}>غير المقروءة فقط</option>
                </select>
                <button class="btn btn-primary btn-sm" type="submit">
                    <i class="tio-filter-list"></i> فلترة
                </button>
                <a href="{{ route('admin.smartads.notifications') }}" class="btn btn-outline-secondary btn-sm">إعادة تعيين</a>
            </form>
        </div>
    </div>

    {{-- Notifications List --}}
    @forelse($notifications as $notif)
        <div class="card notif-card mb-2 {{ $notif->is_read ? 'notif-read' : 'notif-unread' }}"
             id="notif-{{ $notif->id }}">
            <div class="card-body py-3">
                <div class="d-flex align-items-start gap-3">
                    {{-- صورة --}}
                    @if($notif->image_url)
                        <img src="{{ $notif->image_url }}" width="50" height="50"
                             style="border-radius:10px;object-fit:cover;flex-shrink:0">
                    @else
                        <div style="width:50px;height:50px;border-radius:10px;background:#f0f0f0;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                            <i class="tio-notifications text-muted" style="font-size:20px"></i>
                        </div>
                    @endif

                    {{-- المحتوى --}}
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                            <span class="fw-semibold">{{ $notif->title }}</span>
                            @if(!$notif->is_read)
                                <span class="badge badge-soft-primary" style="font-size:10px">جديد</span>
                            @endif
                            <span class="action-badge"
                                  style="background:#f3e5f5;color:#6a1b9a">{{ $notif->action_type }}</span>
                            <span class="badge badge-soft-secondary" style="font-size:10px">{{ $notif->display_type }}</span>
                        </div>
                        @if($notif->body)
                            <div class="text-muted small mb-1">{{ Str::limit($notif->body, 100) }}</div>
                        @endif
                        <div class="d-flex gap-3 text-muted" style="font-size:12px">
                            <span><i class="tio-calendar mr-1"></i>{{ $notif->created_at->diffForHumans() }}</span>
                            @if($notif->expires_at)
                                <span><i class="tio-clock mr-1"></i>ينتهي: {{ $notif->expires_at->format('Y-m-d H:i') }}</span>
                            @endif
                            @if($notif->smart_ad_id)
                                <span>
                                    <i class="tio-link mr-1"></i>
                                    <a href="{{ route('admin.smartads.edit', $notif->smart_ad_id) }}" class="text-muted">
                                        إعلان #{{ $notif->smart_ad_id }}
                                    </a>
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- أزرار --}}
                    <div class="d-flex flex-column gap-1" style="flex-shrink:0">
                        @if(!$notif->is_read)
                            <button class="btn btn-sm btn-outline-primary mark-read-btn"
                                    data-id="{{ $notif->id }}">
                                <i class="tio-checkmark-circle-outlined"></i>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="text-center py-5">
            <i class="tio-notifications-outlined" style="font-size:60px;color:#ddd"></i>
            <div class="text-muted mt-2">لا توجد إشعارات</div>
        </div>
    @endforelse

    {{-- Pagination --}}
    <div class="mt-3">{!! $notifications->appends(request()->query())->links() !!}</div>
</div>

@push('script')
<script>
// Mark single as read
document.querySelectorAll('.mark-read-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        fetch(`/api/v1/smartads/notifications/${id}/read`, {
            method: 'POST',
            headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'}
        }).then(() => {
            const card = document.getElementById(`notif-${id}`);
            card.classList.remove('notif-unread');
            card.classList.add('notif-read');
            this.remove();
        });
    });
});

// Mark all as read
document.getElementById('read-all-btn')?.addEventListener('click', function() {
    if (!confirm('تحديد جميع الإشعارات كمقروءة؟')) return;
    fetch('/api/v1/smartads/notifications/read-all', {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
        body: JSON.stringify({})
    }).then(() => location.reload());
});
</script>
@endpush
@endsection
