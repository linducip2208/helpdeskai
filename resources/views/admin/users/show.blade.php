@extends('layouts.admin')
@section('title', 'User Detail')
@section('content')

<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <a href="{{ route('admin.users.index') }}" class="text-sm text-indigo-600 hover:text-indigo-700">&larr; Back to Users</a>
            <h2 class="text-2xl font-bold text-slate-900 mt-1">{{ $user->name }}</h2>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.users.edit', $user) }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg">Edit</a>
            <form action="{{ route('admin.users.impersonate', $user) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium rounded-lg">Impersonate</button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="md:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
            <h3 class="text-sm font-semibold text-slate-900 uppercase tracking-wider">Profile</h3>
            <dl class="grid grid-cols-2 gap-4 text-sm">
                <div><dt class="text-slate-500">Email</dt><dd class="text-slate-900 mt-1">{{ $user->email }}</dd></div>
                <div><dt class="text-slate-500">Email Verified</dt><dd class="text-slate-900 mt-1">{{ $user->email_verified_at ? $user->email_verified_at->format('M d, Y') : '—' }}</dd></div>
                <div><dt class="text-slate-500">Status</dt><dd class="mt-1"><span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium {{ $user->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-700' }}">{{ $user->is_active ? 'Active' : 'Inactive' }}</span></dd></div>
                <div><dt class="text-slate-500">Joined</dt><dd class="text-slate-900 mt-1">{{ $user->created_at->format('M d, Y') }}</dd></div>
            </dl>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-sm font-semibold text-slate-900 uppercase tracking-wider mb-3">Roles</h3>
            <div class="space-y-2">
                @forelse($user->roles as $role)
                    <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700">{{ ucfirst($role->name) }}</span>
                @empty
                    <p class="text-sm text-slate-400">No roles assigned</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-slate-900 uppercase tracking-wider">Recent Tickets</h3>
        </div>
        <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Subject</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Created</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($user->tickets as $ticket)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-3 text-sm"><a href="{{ route('admin.tickets.show', $ticket) }}" class="text-indigo-600 hover:text-indigo-700">{{ $ticket->subject }}</a></td>
                        <td class="px-6 py-3 text-sm text-slate-500">{{ ucfirst($ticket->status->value ?? $ticket->status) }}</td>
                        <td class="px-6 py-3 text-sm text-slate-500">{{ $ticket->created_at->format('M d, Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-6 py-8 text-center text-sm text-slate-400">No tickets</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>

@endsection
