@extends('layouts.admin')
@section('title', __('Edit Problem'))
@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <a href="{{ route('admin.problems.show', $problem) }}" class="btn btn-link px-0">&larr; {{ __('Back to Problem') }}</a>
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.problems.update', $problem) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label for="title" class="form-label">{{ __('Title') }}</label>
                        <input type="text" name="title" id="title" value="{{ old('title', $problem->title) }}" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="symptoms" class="form-label">{{ __('Symptoms') }}</label>
                        <textarea name="symptoms" id="symptoms" rows="3" class="form-control">{{ old('symptoms', $problem->symptoms) }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label for="root_cause" class="form-label">{{ __('Root cause') }}</label>
                        <textarea name="root_cause" id="root_cause" rows="2" class="form-control">{{ old('root_cause', $problem->root_cause) }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label for="workaround" class="form-label">{{ __('Workaround') }}</label>
                        <textarea name="workaround" id="workaround" rows="2" class="form-control">{{ old('workaround', $problem->workaround) }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label for="permanent_fix" class="form-label">{{ __('Permanent fix') }}</label>
                        <textarea name="permanent_fix" id="permanent_fix" rows="2" class="form-control">{{ old('permanent_fix', $problem->permanent_fix) }}</textarea>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="owner_id" class="form-label">{{ __('Owner') }}</label>
                                <select name="owner_id" id="owner_id" class="form-select">
                                    <option value="">— {{ __('Unassigned') }} —</option>
                                    @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('owner_id', $problem->owner_id) == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="knowledge_article_id" class="form-label">{{ __('Knowledge article') }}</label>
                                <select name="knowledge_article_id" id="knowledge_article_id" class="form-select">
                                    <option value="">— {{ __('None') }} —</option>
                                    @foreach($articles as $article)
                                    <option value="{{ $article->id }}" {{ old('knowledge_article_id', $problem->knowledge_article_id) == $article->id ? 'selected' : '' }}>{{ $article->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-footer d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.problems.show', $problem) }}" class="btn">{{ __('Cancel') }}</a>
                        <button type="submit" class="btn btn-primary">{{ __('Update Problem') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
