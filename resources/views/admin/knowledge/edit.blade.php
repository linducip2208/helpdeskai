@extends('layouts.admin')
@section('title', 'Edit Article')
@section('content')

<div class="max-w-3xl mx-auto space-y-6">
    <div>
        <a href="{{ route('admin.knowledge.index') }}" class="text-sm text-indigo-600 hover:text-indigo-700">&larr; Back to Knowledge Base</a>
        <h2 class="text-2xl font-bold text-slate-900 mt-1">Edit Article</h2>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form action="{{ route('admin.knowledge.update', $article ?? 0) }}" method="POST" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="space-y-5">
                <div>
                    <label for="title" class="block text-sm font-medium text-slate-700 mb-1">Title</label>
                    <input type="text" name="title" id="title" value="{{ old('title', $article->title ?? '') }}"
                           class="w-full rounded-lg border-gray-200 text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                </div>
                <div>
                    <label for="category_id" class="block text-sm font-medium text-slate-700 mb-1">Category</label>
                    <select name="category_id" id="category_id" class="w-full rounded-lg border-gray-200 text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                        @foreach($categories ?? [] as $category)
                        <option value="{{ $category->id }}" {{ old('category_id', $article->category_id ?? '') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="excerpt" class="block text-sm font-medium text-slate-700 mb-1">Excerpt</label>
                    <textarea name="excerpt" id="excerpt" rows="2" class="w-full rounded-lg border-gray-200 text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('excerpt', $article->excerpt ?? '') }}</textarea>
                </div>
                <div>
                    <label for="body" class="block text-sm font-medium text-slate-700 mb-1">Content</label>
                    <textarea name="body" id="body" rows="10" class="w-full rounded-lg border-gray-200 text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>{{ old('body', $article->body ?? '') }}</textarea>
                </div>
                <div class="flex items-center space-x-6">
                    <label class="inline-flex items-center text-sm text-slate-700">
                        <input type="checkbox" name="is_published" value="1" {{ ($article->is_published ?? false) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 mr-2">
                        Published
                    </label>
                    <label class="inline-flex items-center text-sm text-slate-700">
                        <input type="checkbox" name="is_featured" value="1" {{ ($article->is_featured ?? false) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 mr-2">
                        Featured
                    </label>
                </div>
                <div>
                    <label for="tags" class="block text-sm font-medium text-slate-700 mb-1">Tags</label>
                    <input type="text" name="tags" id="tags" value="{{ old('tags', $article->tags ?? '') }}" placeholder="tag1, tag2, tag3"
                           class="w-full rounded-lg border-gray-200 text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>
                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                    <a href="{{ route('admin.knowledge.index') }}" class="px-6 py-2.5 border border-gray-200 text-slate-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition">Cancel</a>
                    <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">Update Article</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection
