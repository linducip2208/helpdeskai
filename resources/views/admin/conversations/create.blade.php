@extends('layouts.admin')
@section('title', 'New Conversation')
@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="mb-3">
            <a href="{{ route('admin.conversations.index') }}">&larr; Back to Conversations</a>
            <h2 class="page-title mt-1">New Conversation</h2>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.conversations.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="user_id" class="form-label">Customer</label>
                        <select name="user_id" id="user_id" class="form-select" required>
                            <option value="">— Select Customer —</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ old('user_id') == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="subject" class="form-label">Subject</label>
                        <input type="text" name="subject" id="subject" value="{{ old('subject') }}" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="body" class="form-label">Initial Message</label>
                        <textarea name="body" id="body" rows="5" class="form-control" required>{{ old('body') }}</textarea>
                    </div>
                    <div class="card-footer d-flex justify-content-end">
                        <a href="{{ route('admin.conversations.index') }}" class="btn me-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">Start Conversation</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
