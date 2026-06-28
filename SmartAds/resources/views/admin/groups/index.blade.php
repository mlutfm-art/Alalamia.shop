@extends('layouts.admin.app')
@section('content')
<div class="container">
    <h3>مجموعات الإشعارات</h3>
    <a href="{{ url('/admin/smartads/groups/create') }}" class="btn btn-primary">إضافة مجموعة</a>
    <table class="table">
        <thead><tr><th>الاسم</th><th>النوع</th><th>الوصف</th><th>إجراءات</th></tr></thead>
        <tbody>
            @foreach($groups as $group)
            <tr>
                <td>{{ $group->name ?? 'بدون اسم' }}</td>
                <td>{{ $group->type ?? 'غير محدد' }}</td>
                <td>{{ $group->description ?? '-' }}</td>
                <td>
                    <a href="{{ url('/admin/smartads/groups/' . $group->id . '/members') }}" class="btn btn-sm btn-info">الأعضاء</a>
                    <a href="{{ url('/admin/smartads/groups/' . $group->id . '/edit') }}" class="btn btn-sm btn-warning">تعديل</a>
                    <a href="{{ url('/admin/smartads/groups/' . $group->id . '/send') }}" class="btn btn-sm btn-success">إرسال إشعار</a>
                    <form action="{{ url('/admin/smartads/groups/' . $group->id) }}" method="POST" style="display:inline">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-danger" onclick="return confirm('متأكد؟')">حذف</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    {{ $groups->links() }}
</div>
@endsection
