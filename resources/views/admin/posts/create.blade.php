@extends('layouts.admin')
@section('title', 'Create Blog Post')
@section('content')

<div class="row justify-content-center">
    <div class="col-12 col-lg-10">
        <a href="{{ route('admin.posts.index') }}" class="btn btn-link px-0">&larr; Back to Posts</a>
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.posts.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" name="title" id="title" value="{{ old('title') }}" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="excerpt" class="form-label">Excerpt</label>
                        <textarea name="excerpt" id="excerpt" rows="2" class="form-control">{{ old('excerpt') }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label for="body" class="form-label">Content</label>
                        <textarea name="body" id="body" rows="10" class="form-control" required>{{ old('body') }}</textarea>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="category_id" class="form-label">Category</label>
                                <select name="category_id" id="category_id" class="form-select">
                                    <option value="">Select Category</option>
                                    @foreach($categories ?? [] as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="published_at" class="form-label">Publish Date</label>
                                <input type="datetime-local" name="published_at" id="published_at" value="{{ old('published_at') }}" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-check form-switch">
                                    <input type="checkbox" name="is_published" value="1" class="form-check-input">
                                    <span class="form-check-label">Published</span>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-check form-switch">
                                    <input type="checkbox" name="is_featured" value="1" class="form-check-input">
                                    <span class="form-check-label">Featured</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="form-footer d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.posts.index') }}" class="btn">Cancel</a>
                        <button type="submit" class="btn btn-primary">Create Post</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
