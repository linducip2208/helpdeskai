@extends('layouts.admin')
@section('title', 'Edit Article')
@section('content')

<div class="row justify-content-center">
    <div class="col-12 col-lg-10">
        <a href="{{ route('admin.knowledge.index') }}" class="btn btn-link px-0">&larr; Back to Knowledge Base</a>
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.knowledge.update', $article ?? 0) }}" method="POST" enctype="multipart/form-data">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" name="title" id="title" value="{{ old('title', $article->title ?? '') }}" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Category</label>
                        <select name="category_id" id="category_id" class="form-select" required>
                            @foreach($categories ?? [] as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $article->category_id ?? '') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="excerpt" class="form-label">Excerpt</label>
                        <textarea name="excerpt" id="excerpt" rows="2" class="form-control">{{ old('excerpt', $article->excerpt ?? '') }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label for="body" class="form-label">Content</label>
                        <textarea name="body" id="body" rows="10" class="form-control" required>{{ old('body', $article->body ?? '') }}</textarea>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-check form-switch">
                                    <input type="checkbox" name="is_published" value="1" {{ ($article->is_published ?? false) ? 'checked' : '' }} class="form-check-input">
                                    <span class="form-check-label">Published</span>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-check form-switch">
                                    <input type="checkbox" name="is_featured" value="1" {{ ($article->is_featured ?? false) ? 'checked' : '' }} class="form-check-input">
                                    <span class="form-check-label">Featured</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="tags" class="form-label">Tags</label>
                        <input type="text" name="tags" id="tags" value="{{ old('tags', $article->tags ?? '') }}" placeholder="tag1, tag2, tag3" class="form-control">
                    </div>
                    <div class="form-footer d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.knowledge.index') }}" class="btn">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Article</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
