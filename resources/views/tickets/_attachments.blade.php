@if(($attachments ?? collect())->count() > 0)
<div class="mt-2">
    <p class="form-label mb-1">{{ __('Attachments') }}</p>
    <div class="d-flex flex-wrap gap-1">
        @foreach($attachments as $attachment)
        <a href="{{ route($downloadRoute, $attachment) }}" class="btn btn-sm" title="{{ __('Download :name', ['name' => $attachment->original_name]) }}">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21.44 11.05l-9.19 9.19a6 6 0 01-8.49-8.49l9.19-9.19a4 4 0 015.66 5.66l-9.2 9.19a2 2 0 01-2.83-2.83l8.49-8.48"/></svg>
            {{ $attachment->original_name }}
            <span class="text-muted">({{ number_format(($attachment->size ?? 0) / 1024, 0) }} KB)</span>
        </a>
        @endforeach
    </div>
</div>
@endif
