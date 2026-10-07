@extends('layouts.admin')
@section('title', 'AI Budgets')
@section('content')

<p class="text-muted mb-3">Dispatches are blocked (with fallback to the next provider) once spend reaches a budget. Spend is computed from successful usage logs.</p>

<div class="row">
    <div class="col-12 col-lg-8">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Budgets</h3></div>
            <div class="table-responsive"><table class="table table-vcenter card-table">
                <thead><tr><th>Scope</th><th>Period</th><th>Limit</th><th>Spent</th><th>Remaining</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                    @forelse($budgets ?? [] as $budget)
                    @php $sp = ($spending[$budget->label()] ?? null); @endphp
                    <tr>
                        <td>
                            <span class="badge bg-blue-lt">{{ $budget->scope }}</span>
                            @if($budget->scope_id)<span class="text-muted small font-monospace">{{ $budget->scope_id }}</span>@endif
                        </td>
                        <td class="text-muted">{{ ucfirst($budget->period) }}</td>
                        <td class="text-muted">${{ number_format($budget->limit_usd, 2) }}</td>
                        <td class="text-muted">${{ number_format($sp['spent'] ?? 0, 4) }}</td>
                        <td>
                            @if($sp && $sp['remaining'] <= 0)
                            <span class="badge bg-red-lt">exhausted</span>
                            @else
                            <span class="text-muted">${{ number_format($sp['remaining'] ?? $budget->limit_usd, 4) }}</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <form action="{{ route('admin.ai-budgets.destroy', $budget) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove this budget?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Remove</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6"><div class="empty"><p class="empty-title">No budgets yet.</p><p class="empty-subtitle text-muted">Without budgets, AI spend is unlimited.</p></div></td></tr>
                    @endforelse
                </tbody>
            </table></div>
        </div>
    </div>
    <div class="col-12 col-lg-4">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Add Budget</h3></div>
            <div class="card-body">
                <form action="{{ route('admin.ai-budgets.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" for="scope">Scope</label>
                        <select name="scope" id="scope" class="form-select">
                            <option value="global">Global</option>
                            <option value="provider">Provider</option>
                            <option value="feature">Feature</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="scope_id">Target (provider ID or feature key)</label>
                        <input type="text" name="scope_id" id="scope_id" value="{{ old('scope_id') }}" class="form-control" placeholder="e.g. 3 or ticket.classify">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="period">Period</label>
                        <select name="period" id="period" class="form-select">
                            <option value="daily">Daily</option>
                            <option value="monthly">Monthly</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="limit_usd">Limit (USD)</label>
                        <input type="number" step="0.0001" min="0.0001" name="limit_usd" id="limit_usd" value="{{ old('limit_usd') }}" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Save Budget</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
