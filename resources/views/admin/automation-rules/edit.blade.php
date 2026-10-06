@extends('layouts.admin')
@section('title', 'Edit Automation Rule')
@section('content')

<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="mb-3">
            <a href="{{ route('admin.automation-rules.index') }}">&larr; Back to Automation Rules</a>
        </div>

        <form action="{{ route('admin.automation-rules.update', $rule) }}" method="POST">
            @csrf @method('PUT')
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Automation Rule</h3>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Rule Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $rule->name) }}" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea name="description" id="description" rows="2" class="form-control">{{ old('description', $rule->description) }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label for="trigger_event" class="form-label">Trigger Event</label>
                        <select name="trigger_event" id="trigger_event" class="form-select" required>
                            @foreach(['ticket_created' => 'Ticket Created', 'ticket_updated' => 'Ticket Updated', 'ticket_replied' => 'Reply Posted', 'ticket_status_changed' => 'Status Changed'] as $k => $v)
                                <option value="{{ $k }}" {{ $rule->trigger_event === $k ? 'selected' : '' }}>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="conditions" class="form-label">Conditions (JSON)</label>
                        <textarea name="conditions_json" id="conditions" rows="4" class="form-control font-monospace">{{ old('conditions_json', json_encode($rule->conditions ?? new \stdClass())) }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label for="actions" class="form-label">Actions (JSON)</label>
                        <textarea name="actions_json" id="actions" rows="4" class="form-control font-monospace">{{ old('actions_json', json_encode($rule->actions ?? [])) }}</textarea>
                    </div>
                    <div class="row g-2">
                        <div class="col-sm-6">
                            <label for="sort_order" class="form-label">Sort Order</label>
                            <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', $rule->sort_order) }}" class="form-control">
                        </div>
                        <div class="col-sm-6 d-flex align-items-end">
                            <label class="form-check">
                                <input type="checkbox" name="is_active" value="1" {{ $rule->is_active ? 'checked' : '' }} class="form-check-input">
                                <span class="form-check-label">Active</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.automation-rules.index') }}" class="btn">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Rule</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.querySelector('form').addEventListener('submit', function(e) {
    try {
        const c = document.getElementById('conditions').value.trim() || '{}';
        const a = document.getElementById('actions').value.trim() || '[]';
        const cP = JSON.parse(c);
        const aP = JSON.parse(a);
        const form = e.target;
        Object.entries(cP).forEach(([k,v]) => {
            const i = document.createElement('input'); i.type='hidden'; i.name=`conditions[${k}]`; i.value=v; form.appendChild(i);
        });
        if (Array.isArray(aP)) {
            aP.forEach((item, idx) => {
                Object.entries(item).forEach(([k,v]) => {
                    const i = document.createElement('input'); i.type='hidden'; i.name=`actions[${idx}][${k}]`; i.value=v; form.appendChild(i);
                });
            });
        }
    } catch(err) {
        e.preventDefault();
        alert('Invalid JSON in conditions or actions: ' + err.message);
    }
});
</script>

@endsection
