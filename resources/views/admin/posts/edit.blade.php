@extends('layouts.admin')
@section('title', 'Edit Blog Post')
@section('content')

<div class="max-w-3xl mx-auto space-y-6">
    <div>
        <a href="{{ route('admin.posts.index') }}" class="text-sm text-indigo-600 hover:text-indigo-700">&larr; Back to Posts</a>
        <h2 class="text-2xl font-bold text-slate-900 mt-1">Edit Blog Post</h2>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form action="{{ route('admin.posts.update', $post ?? 0) }}" method="POST" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="space-y-5">
                <div>
                    <label for="title" class="block text-sm font-medium text-slate-700 mb-1">Title</label>
                    <input type="text" name="title" id="title" value="{{ old('title', $post->title ?? '') }}" class="w-full rounded-lg border-gray-200 text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                </div>
                <div>
                    <label for="excerpt" class="block text-sm font-medium text-slate-700 mb-1">Excerpt</label>
                    <textarea name="excerpt" id="excerpt" rows="2" class="w-full rounded-lg border-gray-200 text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('excerpt', $post->excerpt ?? '') }}</textarea>
                </div>
                <div>
                    <label for="body" class="block text-sm font-medium text-slate-700 mb-1">Content</label>
                    <textarea name="body" id="body" rows="10" class="w-full rounded-lg border-gray-200 text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>{{ old('body', $post->body ?? '') }}</textarea>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="category_id" class="block text-sm font-medium text-slate-700 mb-1">Category</label>
                        <select name="category_id" id="category_id" class="w-full rounded-lg border-gray-200 text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            @foreach($categories ?? [] as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $post->category_id ?? '') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="published_at" class="block text-sm font-medium text-slate-700 mb-1">Publish Date</label>
                        <input type="datetime-local" name="published_at" id="published_at" value="{{ old('published_at', optional($post->published_at ?? null)->format('Y-m-d\TH:i')) }}" class="w-full rounded-lg border-gray-200 text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>
                </div>
                <div class="flex items-center space-x-6">
                    <label class="inline-flex items-center text-sm text-slate-700">
                        <input type="checkbox" name="is_published" value="1" {{ ($post->is_published ?? false) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 mr-2">
                        Published
                    </label>
                    <label class="inline-flex items-center text-sm text-slate-700">
                        <input type="checkbox" name="is_featured" value="1" {{ ($post->is_featured ?? false) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 mr-2">
                        Featured
                    </label>
                </div>
                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                    <a href="{{ route('admin.posts.index') }}" class="px-6 py-2.5 border border-gray-200 text-slate-700 text-sm font-medium rounded-lg hover:bg-gray-50">Cancel</a>
                    <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg">Update Post</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection
