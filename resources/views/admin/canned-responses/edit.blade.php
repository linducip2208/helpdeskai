@extends('layouts.admin')
@section('title', 'Edit Canned Response')
@section('content')

<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="mb-3">
            <a href="{{ route('admin.canned-responses.index') }}">&larr; Back to Canned Responses</a>
        </div>

        <form action="{{ route('admin.canned-responses.update', $cannedResponse) }}" method="POST">
            @csrf @method('PUT')
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Canned Response</h3>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" name="title" id="title" value="{{ old('title', $cannedResponse->title) }}" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Category</label>
                        <select name="category_id" id="category_id" class="form-select">
                            <option value="">— None —</option>
                            @foreach($categories ?? [] as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id', $cannedResponse->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="body" class="form-label">Response Body</label>
                        <textarea name="body" id="body" rows="8" class="form-control" required>{{ old('body', $cannedResponse->body) }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-check">
                            <input type="checkbox" name="is_active" value="1" {{ $cannedResponse->is_active ? 'checked' : '' }} class="form-check-input">
                            <span class="form-check-label">Active</span>
                        </label>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.canned-responses.index') }}" class="btn">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Response</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection
