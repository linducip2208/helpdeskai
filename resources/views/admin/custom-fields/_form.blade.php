<div class="card">
    <div class="card-header"><h3 class="card-title">{{ $field ? __('Edit Custom Field') : __('Add Custom Field') }}</h3></div>
    <div class="card-body">
        <div class="row g-2">
            <div class="col-md-6">
                <label class="form-label" for="label">{{ __('Label') }}</label>
                <input type="text" name="label" id="label" value="{{ old('label', $field->label ?? '') }}" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="name">{{ __('Field name (slug)') }}</label>
                <input type="text" name="name" id="name" value="{{ old('name', $field->name ?? '') }}" class="form-control font-monospace" required>
            </div>
        </div>
        <div class="row g-2 mt-1">
            <div class="col-md-6">
                <label class="form-label" for="type">{{ __('Type') }}</label>
                <select name="type" id="type" class="form-select">
                    @foreach(\App\Models\TicketCustomField::TYPES as $type)
                    <option value="{{ $type }}" {{ old('type', $field->type ?? 'text') === $type ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="department_id">{{ __('Department (empty = all)') }}</label>
                <select name="department_id" id="department_id" class="form-select">
                    <option value="">{{ __('All departments') }}</option>
                    @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ (string) old('department_id', $field->department_id ?? '') === (string) $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="mb-3 mt-3">
            <label class="form-label" for="options_text">{{ __('Options (one per line, for select type)') }}</label>
            <textarea name="options_text" id="options_text" rows="3" class="form-control font-monospace">{{ old('options_text', isset($field) && $field ? implode("\n", $field->optionList()) : '') }}</textarea>
        </div>
        <div class="row g-2">
            <div class="col-md-4">
                <label class="form-label" for="sort_order">{{ __('Sort order') }}</label>
                <input type="number" name="sort_order" id="sort_order" min="0" value="{{ old('sort_order', $field->sort_order ?? 0) }}" class="form-control">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <label class="form-check">
                    <input type="checkbox" name="is_required" value="1" {{ old('is_required', $field->is_required ?? false) ? 'checked' : '' }} class="form-check-input">
                    <span class="form-check-label">{{ __('Required') }}</span>
                </label>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <label class="form-check">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $field->is_active ?? true) ? 'checked' : '' }} class="form-check-input">
                    <span class="form-check-label">{{ __('Active') }}</span>
                </label>
            </div>
        </div>
    </div>
    <div class="card-footer d-flex justify-content-end gap-2">
        <a href="{{ route('admin.custom-fields.index') }}" class="btn">{{ __('Cancel') }}</a>
        <button type="submit" class="btn btn-primary">{{ __('Save Field') }}</button>
    </div>
</div>
