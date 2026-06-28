@extends('layouts.admin.app')
@section('content')
<div class="container">
    <h3>أعضاء مجموعة: {{ $group->name ?? 'بدون اسم' }}</h3>
    <div class="row">
        <div class="col-md-6">
            <h4>الأعضاء الحاليون</h4>
            <table class="table">
                <thead><tr><th>الاسم</th><th>البريد</th><th>الهاتف</th><th>حذف</th></tr></thead>
                <tbody>
                    @foreach($group->users as $user)
                    <tr>
                        <td>{{ ($user->f_name ?? '') . ' ' . ($user->l_name ?? '') }}</td>
                        <td>{{ $user->email ?? '-' }}</td>
                        <td>{{ $user->phone ?? '-' }}</td>
                        <td>
                            <form method="POST" action="{{ url('/admin/smartads/groups/' . $group->id . '/members/remove') }}">
                                @csrf
                                <input type="hidden" name="user_id" value="{{ $user->id }}">
                                <button class="btn btn-sm btn-danger">إزالة</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="col-md-6">
            <h4>إضافة عضو</h4>
            <form method="POST" action="{{ url('/admin/smartads/groups/' . $group->id . '/members/add') }}">
                @csrf
                <select name="user_id" class="form-control">
                    @foreach($allUsers as $u)
                        @unless($group->users->contains('id', $u->id))
                            <option value="{{ $u->id }}">{{ ($u->f_name ?? '') . ' ' . ($u->l_name ?? '') }} ({{ $u->email ?? 'بدون بريد' }})</option>
                        @endunless
                    @endforeach
                </select>
                <button class="btn btn-primary mt-2">إضافة</button>
            </form>
        </div>
    </div>
</div>
@endsection
