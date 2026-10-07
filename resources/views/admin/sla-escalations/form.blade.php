@extends('layouts.admin')
@section('title', isset($rule) ? __('Edit Escalation Rule') : __('New Escalation Rule'))
@section('content')

<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="mb-3"><a href="{{ route('admin.sla-escalations.index') }}">&larr; {{ __('Back to Escalations') }}</a></div>
        <form action="{{ isset($rule) ? route('admin.sla-escalations.update', $rule) : route('admin.sla-escalations.store') }}" method="POST">
            @csrf
            @if(isset($rule)) @method('PUT') @endif
            <div class="card">
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label" for="name">{{ __('Name') }}</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $rule->name ?? '') }}" class="form-control" required>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label" for="trigger">{{ __('Trigger') }}</label>
                            <select name="trigger" id="trigger" class="form-select">
                                @foreach(\App\Models\SlaEscalationRule::TRIGGERS as $trigger)
                                <option value="{{ $trigger }}" {{ old('trigger', $rule->trigger ?? '') === $trigger ? 'selected' : '' }}>{{ $trigger }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="after_minutes">{{ __('After (minutes)') }}</label>
                            <input type="number" name="after_minutes" id="after_minutes" min="0" max="10080" value="{{ old('after_minutes', $rule->after_minutes ?? 30) }}" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="action_priority">{{ __('Set priority (optional)') }}</label>
                            <select name="action_priority" id="action_priority" class="form-select">
                                <option value="">{{ __('No change') }}</option>
                                @foreach(['low', 'medium', 'high', 'urgent'] as $p)
                                <option value="{{ $p }}" {{ old('action_priority', $rule->action_priority ?? '') === $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="action_assign_role">{{ __('Reassign to role (optional)') }}</label>
                            <select name="action_assign_role" id="action_assign_role" class="form-select">
                                <option value="">{{ __('No change') }}</option>
                                @foreach(['agent', 'manager', 'admin'] as $r)
                                <option value="{{ $r }}" {{ old('action_assign_role', $rule->action_assign_role ?? '') === $r ? 'selected' : '' }}>{{ ucfirst($r) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row g-2 mt-1">
                        <div class="col-md-6 d-flex align-items-end">
                            <label class="form-check">
                                <input type="checkbox" name="notify_assignee" value="1" {{ old('notify_assignee', $rule->notify_assignee ?? true) ? 'checked' : '' }} class="form-check-input">
                                <span class="form-check-label">{{ __('Notify assignee') }}</span>
                            </label>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <label class="form-check">
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $rule->is_active ?? true) ? 'checked' : '' }} class="form-check-input">
                                <span class="form-check-label">{{ __('Active') }}</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.sla-escalations.index') }}" class="btn">{{ __('Cancel') }}</a>
                    <button type="submit" class="btn btn-primary">{{ __('Save Rule') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
