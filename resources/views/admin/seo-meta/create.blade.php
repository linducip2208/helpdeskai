@extends('layouts.admin')
@section('title', 'Add SEO Override')
@section('content')

<div class="row justify-content-center">
    <div class="col-12 col-lg-10">
        <a href="{{ route('admin.seo-meta.index') }}" class="btn btn-link px-0">&larr; Back</a>
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.seo-meta.store') }}">
                    @csrf
                    @include('admin.seo-meta._form', ['item' => null])
                    <div class="form-footer d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.seo-meta.index') }}" class="btn">Cancel</a>
                        <button class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
