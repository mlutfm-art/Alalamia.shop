@extends('layouts.admin.app')
@section('content')
<div class="container">
    <h3>تعديل المجموعة</h3>
    <form method="POST" action="{{ url('/admin/smartads/groups/' . $group->id) }}">
        @csrf @method('PUT')
        <div class="mb-3">
            <label>الاسم</label>
            <input type="text" name="name" class="form-control" value="{{ $group->name }}" required>
        </div>
        <div class="mb-3">
            <label>النوع</label>
            <select name="type" class="form-control">
                <option value="admin" {{ $group->type=='admin'?'selected':'' }}>إدارة</option>
                <option value="special" {{ $group->type=='special'?'selected':'' }}>خاصة</option>
                <option value="custom" {{ $group->type=='custom'?'selected':'' }}>مخصصة</option>
            </select>
        </div>
        <div class="mb-3">
            <label>الوصف</label>
            <textarea name="description" class="form-control">{{ $group->description }}</textarea>
        </div>
        <button class="btn btn-primary">تحديث</button>
    </form>
</div>
@endsection
