@extends('layouts.admin')
@section('title', 'Knowledge Base FAQs')
@section('page-actions')
<button type="button" onclick="document.getElementById('create-modal').style.display='flex'" class="btn btn-primary">
    Add FAQ
</button>
@endsection
@section('content')

<div class="card">
    <div class="table-responsive">
        <table class="table table-vcenter card-table">
            <thead>
                <tr>
                    <th>Question</th>
                    <th>Category</th>
                    <th>Order</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($faqs ?? [] as $faq)
                <tr>
                    <td>{{ Str::limit($faq->question, 80) }}</td>
                    <td class="text-muted">{{ $faq->category->name ?? 'N/A' }}</td>
                    <td class="text-muted">{{ $faq->sort_order ?? 0 }}</td>
                    <td>
                        @if($faq->is_active ?? true)
                            <span class="badge bg-green-lt">Active</span>
                        @else
                            <span class="badge bg-secondary">Inactive</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <a href="{{ route('admin.knowledge-faqs.edit', $faq) }}" class="btn btn-sm">Edit</a>
                        <form action="{{ route('admin.knowledge-faqs.destroy', $faq) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5"><div class="empty"><p class="empty-title">No FAQs found.</p><p class="empty-subtitle text-muted">Add your first FAQ to get started.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div id="create-modal" style="display:none;position:fixed;inset:0;z-index:50;align-items:center;justify-content:center;background:rgba(0,0,0,.5);">
    <div class="card" style="width:100%;max-width:32rem;margin:0 1rem;">
        <div class="card-header">
            <h3 class="card-title">Add FAQ</h3>
            <div class="card-actions">
                <button onclick="document.getElementById('create-modal').style.display='none'" class="btn btn-sm">&times;</button>
            </div>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.knowledge-faqs.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Question</label>
                    <input type="text" name="question" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Answer</label>
                    <textarea name="answer" rows="4" class="form-control" required></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Category</label>
                    <select name="category_id" class="form-select">
                        <option value="">Select Category</option>
                        @foreach($categories ?? [] as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-footer d-flex justify-content-end gap-2">
                    <button type="button" onclick="document.getElementById('create-modal').style.display='none'" class="btn">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
