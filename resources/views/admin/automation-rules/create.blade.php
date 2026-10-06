@extends('layouts.admin')
@section('title', 'Add Automation Rule')
@section('content')

<div class="max-w-2xl mx-auto space-y-6">
    <div>
        <a href="{{ route('admin.automation-rules.index') }}" class="text-sm text-indigo-600 hover:text-indigo-700">&larr; Back to Automation Rules</a>
        <h2 class="text-2xl font-bold text-slate-900 mt-1">Add Automation Rule</h2>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form action="{{ route('admin.automation-rules.store') }}" method="POST">
            @csrf
            <div class="space-y-5">
                <div>
                    <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Rule Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" class="w-full rounded-lg border-gray-200 text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                </div>
                <div>
                    <label for="description" class="block text-sm font-medium text-slate-700 mb-1">Description</label>
                    <textarea name="description" id="description" rows="2" class="w-full rounded-lg border-gray-200 text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('description') }}</textarea>
                </div>
                <div>
                    <label for="trigger_event" class="block text-sm font-medium text-slate-700 mb-1">Trigger Event</label>
                    <select name="trigger_event" id="trigger_event" class="w-full rounded-lg border-gray-200 text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                        <option value="ticket_created">Ticket Created</option>
                        <option value="ticket_updated">Ticket Updated</option>
                        <option value="ticket_replied">Reply Posted</option>
                        <option value="ticket_status_changed">Status Changed</option>
                    </select>
                </div>
                <div>
                    <label for="conditions" class="block text-sm font-medium text-slate-700 mb-1">Conditions (JSON)</label>
                    <textarea name="conditions_json" id="conditions" rows="4" placeholder='{"priority":"high"}' class="w-full font-mono rounded-lg border-gray-200 text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('conditions_json', '{}') }}</textarea>
                    <p class="mt-1 text-xs text-slate-400">JSON object of conditions that must match for the rule to fire.</p>
                </div>
                <div>
                    <label for="actions" class="block text-sm font-medium text-slate-700 mb-1">Actions (JSON)</label>
                    <textarea name="actions_json" id="actions" rows="4" placeholder='[{"type":"assign","value":"agent@helpdesk.test"}]' class="w-full font-mono rounded-lg border-gray-200 text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('actions_json', '[]') }}</textarea>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="sort_order" class="block text-sm font-medium text-slate-700 mb-1">Sort Order</label>
                        <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', 0) }}" class="w-full rounded-lg border-gray-200 text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>
                    <div class="flex items-end pb-1">
                        <label class="inline-flex items-center text-sm text-slate-700">
                            <input type="checkbox" name="is_active" value="1" checked class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 mr-2">
                            Active
                        </label>
                    </div>
                </div>
                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                    <a href="{{ route('admin.automation-rules.index') }}" class="px-6 py-2.5 border border-gray-200 text-slate-700 text-sm font-medium rounded-lg hover:bg-gray-50">Cancel</a>
                    <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg">Save Rule</button>
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
        let h1 = form.querySelector('input[name="_conditions_hidden"]');
        if (!h1) { h1 = document.createElement('input'); h1.type='hidden'; h1.name='_conditions_hidden'; form.appendChild(h1); }
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
