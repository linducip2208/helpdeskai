<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\TicketCustomField;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TicketCustomFieldController extends Controller
{
    public function index(): View
    {
        return view('admin.custom-fields.index', [
            'fields' => TicketCustomField::with('department:id,name')->orderBy('sort_order')->orderBy('id')->paginate(25),
        ]);
    }

    public function create(): View
    {
        return view('admin.custom-fields.create', [
            'departments' => Department::where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        $validated['name'] = Str::slug($validated['name'], '_');
        $validated['options'] = $this->parseOptions($request->input('options_text'));

        $field = TicketCustomField::create($validated);

        ActivityLogService::logCustom(auth()->id(), 'custom_field_create', TicketCustomField::class, $field->id, $field->label);

        return redirect()->route('admin.custom-fields.index')->with('success', 'Custom field created.');
    }

    public function edit(TicketCustomField $customField): View
    {
        return view('admin.custom-fields.edit', [
            'field' => $customField,
            'departments' => Department::where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, TicketCustomField $customField): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        $validated['name'] = Str::slug($validated['name'], '_');
        $validated['options'] = $this->parseOptions($request->input('options_text'));

        $customField->update($validated);

        ActivityLogService::logCustom(auth()->id(), 'custom_field_update', TicketCustomField::class, $customField->id, $customField->label);

        return redirect()->route('admin.custom-fields.index')->with('success', 'Custom field updated.');
    }

    public function destroy(TicketCustomField $customField): RedirectResponse
    {
        $label = $customField->label;
        $id = $customField->id;
        $customField->delete();

        ActivityLogService::logCustom(auth()->id(), 'custom_field_delete', TicketCustomField::class, $id, $label);

        return redirect()->route('admin.custom-fields.index')->with('success', 'Custom field deleted.');
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'label' => 'required|string|max:255',
            'type' => 'required|string|in:'.implode(',', TicketCustomField::TYPES),
            'department_id' => 'nullable|exists:departments,id',
            'is_required' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
            'options_text' => 'nullable|string',
        ];
    }

    /**
     * @return array<int, string>
     */
    protected function parseOptions(?string $text): array
    {
        if ($text === null || trim($text) === '') {
            return [];
        }

        return array_values(array_filter(array_map(
            fn ($line) => mb_substr(trim($line), 0, 255),
            preg_split('/\r\n|\r|\n/', $text)
        )));
    }
}
