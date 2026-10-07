{{-- Include from profile/edit.blade.php with:
    @include('profile.partials.notification-preferences', [
        'events' => \App\Models\NotificationPreference::EVENTS,
        'channels' => \App\Models\NotificationPreference::CHANNELS,
        'prefs' => \App\Models\NotificationPreference::where('user_id', auth()->id())->get()->keyBy('event'),
    ])
    Form posts to route('profile.notifications.update').
--}}
<div>
    <h3 class="card-title">{{ __('Notification preferences') }}</h3>
    <p class="text-secondary mb-3">{{ __('Choose how you want to be notified for each event.') }}</p>

    <form action="{{ route('profile.notifications.update') }}" method="POST">
        @csrf @method('PUT')
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>{{ __('Event') }}</th>
                        @foreach($channels as $channel)
                            <th class="text-center">{{ strtoupper($channel) }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($events as $event)
                        @php $selected = (array) (optional($prefs[$event] ?? null)->channels ?? ['db', 'email']); @endphp
                        <tr>
                            <td><code>{{ $event }}</code></td>
                            @foreach($channels as $channel)
                                <td class="text-center">
                                    <input type="checkbox" name="preferences[{{ $event }}][]" value="{{ $channel }}"
                                           class="form-check-input" {{ in_array($channel, $selected, true) ? 'checked' : '' }}>
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="form-footer mt-3">
            <button type="submit" class="btn btn-primary btn-sm">{{ __('Save preferences') }}</button>
        </div>
    </form>
</div>
