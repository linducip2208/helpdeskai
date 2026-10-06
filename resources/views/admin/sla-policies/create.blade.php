@extends('layouts.admin')
@section('title', 'Add SLA Policy')
@section('content')

<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <a href="{{ route('admin.sla-policies.index') }}" class="btn btn-link px-0">&larr; Back to SLA Policies</a>
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.sla-policies.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="name" class="form-label">Policy Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" class="form-control" required>
                        @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea name="description" id="description" rows="2" class="form-control">{{ old('description') }}</textarea>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="department_id" class="form-label">Department</label>
                                <select name="department_id" id="department_id" class="form-select" required>
                                    <option value="">— Select Department —</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="priority" class="form-label">Priority</label>
                                <select name="priority" id="priority" class="form-select" required>
                                    <option value="low">Low</option>
                                    <option value="medium" selected>Medium</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="first_response_time" class="form-label">First Response (minutes)</label>
                                <input type="number" min="1" name="first_response_time" id="first_response_time" value="{{ old('first_response_time', 60) }}" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="resolution_time" class="form-label">Resolution Time (minutes)</label>
                                <input type="number" min="1" name="resolution_time" id="resolution_time" value="{{ old('resolution_time', 1440) }}" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-check form-switch">
                            <input type="checkbox" name="is_active" value="1" checked class="form-check-input">
                            <span class="form-check-label">Active</span>
                        </label>
                    </div>
                    <div class="card mt-3 mb-3">
                        <div class="card-header"><h3 class="card-title">Business Hours</h3></div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-check form-switch">
                                    <input type="checkbox" name="use_business_hours" value="1" checked class="form-check-input">
                                    <span class="form-check-label">Count only within business hours</span>
                                </label>
                            </div>
                            <div class="mb-3">
                                <span class="form-label">Workdays</span>
                                <div class="d-flex flex-wrap gap-3">
                                    @foreach([1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat', 7 => 'Sun'] as $d => $label)
                                    <label class="form-check">
                                        <input type="checkbox" name="workdays[]" value="{{ $d }}" {{ in_array($d, old('workdays', [1, 2, 3, 4, 5])) ? 'checked' : '' }} class="form-check-input">
                                        <span class="form-check-label">{{ $label }}</span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <label for="work_start" class="form-label">Work start</label>
                                    <input type="time" name="work_start" id="work_start" value="{{ old('work_start', '08:00') }}" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label for="work_end" class="form-label">Work end</label>
                                    <input type="time" name="work_end" id="work_end" value="{{ old('work_end', '17:00') }}" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label for="timezone" class="form-label">Timezone</label>
                                    <input type="text" name="timezone" id="timezone" value="{{ old('timezone', 'Asia/Jakarta') }}" class="form-control">
                                </div>
                            </div>
                            <div class="form-hint mt-2">Holidays (Automation → Holidays) are always skipped.</div>
                        </div>
                    </div>
                    <div class="form-footer d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.sla-policies.index') }}" class="btn">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save Policy</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
