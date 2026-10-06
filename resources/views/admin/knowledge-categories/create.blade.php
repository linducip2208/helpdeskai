@extends('layouts.admin')
@section('title', 'Add Knowledge Category')
@section('content')

<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <a href="{{ route('admin.knowledge-categories.index') }}" class="btn btn-link px-0">&larr; Back to Knowledge Categories</a>
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.knowledge-categories.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="parent_id" class="form-label">Parent Category</label>
                        <select name="parent_id" id="parent_id" class="form-select">
                            <option value="">— None (top-level) —</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('parent_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea name="description" id="description" rows="3" class="form-control">{{ old('description') }}</textarea>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="sort_order" class="form-label">Sort Order</label>
                                <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', 0) }}" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-check form-switch mt-4">
                                    <input type="checkbox" name="is_active" value="1" checked class="form-check-input">
                                    <span class="form-check-label">Active</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="form-footer d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.knowledge-categories.index') }}" class="btn">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
