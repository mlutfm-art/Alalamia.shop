@extends('layouts.admin.app')
@section('content')
<div class="container">
    <h3>إرسال إشعار إلى مجموعة: {{ $group->name ?? 'بدون اسم' }}</h3>
    <form method="POST" action="{{ url('/admin/smartads/groups/' . $group->id . '/send') }}">
        @csrf
        <div class="mb-3">
            <label>عنوان الإشعار</label>
            <input type="text" name="title" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>نص الإشعار</label>
            <textarea name="body" class="form-control" required></textarea>
        </div>
        <button class="btn btn-success">إرسال</button>
    </form>
</div>
@endsection
