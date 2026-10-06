@if(!empty($customFieldValues))
<div class="mt-2">
    <p class="form-label mb-1">{{ __('Additional Information') }}</p>
    <dl class="row mb-0">
        @foreach($customFieldValues as $label => $value)
        <dt class="col-5 text-muted">{{ $label }}</dt>
        <dd class="col-7">{{ $value }}</dd>
        @endforeach
    </dl>
</div>
@endif
