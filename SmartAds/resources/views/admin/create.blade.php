@extends('layouts.admin.app')

@section('title', __('إنشاء إعلان جديد'))

@section('content')
<div class="content container-fluid">
    <div class="page-header mb-4">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('admin.smartads.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="tio-arrow-backward"></i>
            </a>
            <div>
                <h1 class="page-header-title mb-0">
                    <i class="tio-add-circle-outlined mr-2 text-primary"></i>
                    إنشاء إعلان جديد
                </h1>
                <small class="text-muted">Smart Engagement Engine</small>
            </div>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger mb-3">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <form action="{{ route('admin.smartads.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @include('smartads::admin._form')
        <div class="mt-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary px-4">
                <i class="tio-save mr-1"></i> حفظ الإعلان
            </button>
            <a href="{{ route('admin.smartads.index') }}" class="btn btn-outline-secondary">إلغاء</a>
        </div>
    </form>
</div>
@endsection
