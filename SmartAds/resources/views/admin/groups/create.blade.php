@extends('layouts.admin.app')
@section('content')
<div class="container">
    <h3>إضافة مجموعة جديدة</h3>
    <form method="POST" action="{{ url('/admin/smartads/groups') }}">
        @csrf
        <div class="mb-3">
            <label>الاسم</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>النوع</label>
            <select name="type" class="form-control">
                <option value="admin">إدارة</option>
                <option value="special">خاصة</option>
                <option value="custom">مخصصة</option>
            </select>
        </div>
        <div class="mb-3">
            <label>الوصف</label>
            <textarea name="description" class="form-control"></textarea>
        </div>
        <button class="btn btn-primary">حفظ</button>
    </form>
</div>
@endsection
