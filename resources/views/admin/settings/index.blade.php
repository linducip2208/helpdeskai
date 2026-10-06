@extends('layouts.admin')
@section('title', 'Settings')
@section('content')

<div class="space-y-6">
    <h2 class="text-2xl font-bold text-slate-900">Settings</h2>

    <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
        @csrf @method('PUT')

        <!-- General Settings -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
            <h3 class="text-lg font-semibold text-slate-900 mb-4">General</h3>
            <div class="space-y-4">
                <div>
                    <label for="site_name" class="block text-sm font-medium text-slate-700 mb-1">Site Name</label>
                    <input type="text" name="site_name" id="site_name" value="{{ old('site_name', config('app.name')) }}" class="w-full rounded-lg border-gray-200 text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>
                <div>
                    <label for="site_description" class="block text-sm font-medium text-slate-700 mb-1">Site Description</label>
                    <textarea name="site_description" id="site_description" rows="2" class="w-full rounded-lg border-gray-200 text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('site_description', $settings->site_description ?? '') }}</textarea>
                </div>
                <div>
                    <label for="support_email" class="block text-sm font-medium text-slate-700 mb-1">Support Email</label>
                    <input type="email" name="support_email" id="support_email" value="{{ old('support_email', $settings->support_email ?? '') }}" class="w-full rounded-lg border-gray-200 text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>
            </div>
        </div>

        <!-- Ticket Settings -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
            <h3 class="text-lg font-semibold text-slate-900 mb-4">Ticket Settings</h3>
            <div class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="default_priority" class="block text-sm font-medium text-slate-700 mb-1">Default Priority</label>
                        <select name="default_priority" id="default_priority" class="w-full rounded-lg border-gray-200 text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                    <div>
                        <label for="ticket_prefix" class="block text-sm font-medium text-slate-700 mb-1">Ticket ID Prefix</label>
                        <input type="text" name="ticket_prefix" id="ticket_prefix" value="{{ old('ticket_prefix', $settings->ticket_prefix ?? 'TKT-') }}" class="w-full rounded-lg border-gray-200 text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>
                </div>
                <div>
                    <label for="auto_close_days" class="block text-sm font-medium text-slate-700 mb-1">Auto-close Resolved Tickets After (days)</label>
                    <input type="number" name="auto_close_days" id="auto_close_days" value="{{ old('auto_close_days', $settings->auto_close_days ?? 7) }}" class="w-full rounded-lg border-gray-200 text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>
            </div>
        </div>

        <!-- Notification Settings -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
            <h3 class="text-lg font-semibold text-slate-900 mb-4">Notifications</h3>
            <div class="space-y-3">
                <label class="flex items-center justify-between py-2">
                    <span class="text-sm text-slate-700">Email notifications for new tickets</span>
                    <input type="checkbox" name="notify_new_ticket" value="1" {{ ($settings->notify_new_ticket ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                </label>
                <label class="flex items-center justify-between py-2">
                    <span class="text-sm text-slate-700">Email notifications for ticket replies</span>
                    <input type="checkbox" name="notify_ticket_reply" value="1" {{ ($settings->notify_ticket_reply ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                </label>
                <label class="flex items-center justify-between py-2">
                    <span class="text-sm text-slate-700">SLA breach alerts</span>
                    <input type="checkbox" name="notify_sla_breach" value="1" {{ ($settings->notify_sla_breach ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                </label>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">Save Settings</button>
        </div>
    </form>
</div>

@endsection
