@extends('layouts.admin')
@section('title', 'Add SEO Override')
@section('content')

<div class="max-w-3xl mx-auto space-y-6">
    <div>
        <a href="{{ route('admin.seo-meta.index') }}" class="text-sm text-indigo-600 hover:text-indigo-700">&larr; Back</a>
        <h2 class="text-2xl font-bold text-slate-900 mt-1">Add SEO Override</h2>
    </div>

    <form method="POST" action="{{ route('admin.seo-meta.store') }}" class="bg-white rounded-xl border border-gray-100 p-6 space-y-4">
        @csrf
        @include('admin.seo-meta._form', ['item' => null])
        <div class="flex justify-end gap-2 pt-4 border-t border-gray-100">
            <a href="{{ route('admin.seo-meta.index') }}" class="px-6 py-2.5 border border-gray-200 text-slate-700 text-sm rounded-lg hover:bg-gray-50">Cancel</a>
            <button class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm rounded-lg">Save</button>
        </div>
    </form>
</div>

@endsection
