@if(($customFields ?? collect())->count() > 0)
<div class="card mt-3">
    <div class="card-body">
        <h3 class="card-title">{{ __('Additional Information') }}</h3>
        @foreach($customFields as $cf)
        @php $inputName = 'custom_fields['.$cf->name.']'; $oldVal = old('custom_fields.'.$cf->name); @endphp
        <div class="mb-3" data-custom-field="{{ $cf->name }}">
            @if($cf->type === 'checkbox')
            <label class="form-check">
                <input type="checkbox" name="{{ $inputName }}" value="1" {{ $oldVal ? 'checked' : '' }} class="form-check-input">
                <span class="form-check-label">{{ $cf->label }}@if($cf->is_required) <span class="text-danger">*</span>@endif</span>
            </label>
            @else
            <label class="form-label" for="cf-{{ $cf->name }}">{{ $cf->label }}@if($cf->is_required) <span class="text-danger">*</span>@endif</label>
            @if($cf->type === 'textarea')
            <textarea name="{{ $inputName }}" id="cf-{{ $cf->name }}" rows="3" class="form-control">{{ $oldVal }}</textarea>
            @elseif($cf->type === 'select')
            <select name="{{ $inputName }}" id="cf-{{ $cf->name }}" class="form-select">
                <option value="">—</option>
                @foreach($cf->optionList() as $option)
                <option value="{{ $option }}" {{ (string) $oldVal === (string) $option ? 'selected' : '' }}>{{ $option }}</option>
                @endforeach
            </select>
            @elseif($cf->type === 'number')
            <input type="number" step="any" name="{{ $inputName }}" id="cf-{{ $cf->name }}" value="{{ $oldVal }}" class="form-control">
            @elseif($cf->type === 'date')
            <input type="date" name="{{ $inputName }}" id="cf-{{ $cf->name }}" value="{{ $oldVal }}" class="form-control">
            @else
            <input type="text" name="{{ $inputName }}" id="cf-{{ $cf->name }}" value="{{ $oldVal }}" class="form-control">
            @endif
            @endif
            @error('custom_fields.'.$cf->name)<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
        @endforeach
    </div>
</div>
@endif
<div id="custom-fields-dept"></div>
@if(!empty($fieldsUrl))
<script>
(function () {
    var dept = document.getElementById('department_id');
    var box = document.getElementById('custom-fields-dept');
    if (!dept || !box) return;
    function esc(s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]; }); }
    function render(fields) {
        if (!fields.length) { box.innerHTML = ''; return; }
        var html = '<div class="card mt-3"><div class="card-body"><h3 class="card-title">{{ __('Department Fields') }}</h3>';
        fields.forEach(function (f) {
            var req = f.required ? ' <span class="text-danger">*</span>' : '';
            html += '<div class="mb-3"><label class="form-label">' + esc(f.label) + req + '</label>';
            var name = 'custom_fields[' + f.name + ']';
            if (f.type === 'textarea') html += '<textarea name="' + esc(name) + '" rows="3" class="form-control"></textarea>';
            else if (f.type === 'select') {
                html += '<select name="' + esc(name) + '" class="form-select"><option value="">—</option>';
                (f.options || []).forEach(function (o) { html += '<option value="' + esc(o) + '">' + esc(o) + '</option>'; });
                html += '</select>';
            }
            else if (f.type === 'number') html += '<input type="number" step="any" name="' + esc(name) + '" class="form-control">';
            else if (f.type === 'date') html += '<input type="date" name="' + esc(name) + '" class="form-control">';
            else if (f.type === 'checkbox') html += '<label class="form-check"><input type="checkbox" name="' + esc(name) + '" value="1" class="form-check-input"></label>';
            else html += '<input type="text" name="' + esc(name) + '" class="form-control">';
            html += '</div>';
        });
        box.innerHTML = html + '</div></div>';
    }
    function load() {
        var id = dept.value || '';
        fetch('{{ $fieldsUrl }}?department_id=' + encodeURIComponent(id), { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.ok ? r.json() : { data: [] }; })
            .then(function (j) { render(j.data || []); })
            .catch(function () {});
    }
    dept.addEventListener('change', load);
    load();
})();
</script>
@endif
