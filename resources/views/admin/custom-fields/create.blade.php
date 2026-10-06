@extends('layouts.admin')
@section('title', __('Add Custom Field'))
@section('content')

<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="mb-3"><a href="{{ route('admin.custom-fields.index') }}">&larr; {{ __('Back to Custom Fields') }}</a></div>
        <form action="{{ route('admin.custom-fields.store') }}" method="POST">
            @csrf
            @include('admin.custom-fields._form', ['field' => null, 'departments' => $departments ?? []])
        </form>
    </div>
</div>
@endsection
