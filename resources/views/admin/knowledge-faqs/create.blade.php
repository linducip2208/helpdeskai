@extends('layouts.admin')
@section('title', 'Add FAQ')
@section('content')

<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <a href="{{ route('admin.knowledge-faqs.index') }}" class="btn btn-link px-0">&larr; Back to FAQs</a>
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.knowledge-faqs.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Category</label>
                        <select name="category_id" id="category_id" class="form-select" required>
                            <option value="">— Select Category —</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="question" class="form-label">Question</label>
                        <input type="text" name="question" id="question" value="{{ old('question') }}" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="answer" class="form-label">Answer</label>
                        <textarea name="answer" id="answer" rows="6" class="form-control" required>{{ old('answer') }}</textarea>
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
                        <a href="{{ route('admin.knowledge-faqs.index') }}" class="btn">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save FAQ</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
