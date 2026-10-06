<?php

namespace App\Services;

use App\Models\TicketCustomField;
use Illuminate\Support\Collection;

class CustomFieldService
{
    /**
     * @return Collection<int, TicketCustomField>
     */
    public function forDepartment(?int $departmentId): Collection
    {
        return TicketCustomField::where('is_active', true)
            ->where(function ($q) use ($departmentId) {
                $q->whereNull('department_id');
                if ($departmentId) {
                    $q->orWhere('department_id', $departmentId);
                }
            })
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    /**
     * @return array<string, string>
     */
    public function rules(?int $departmentId): array
    {
        $rules = [];

        foreach ($this->forDepartment($departmentId) as $field) {
            $key = 'custom_fields.'.$field->name;
            $rule = [$field->is_required ? 'required' : 'nullable'];

            $rule[] = match ($field->type) {
                'number' => 'numeric',
                'date' => 'date',
                'select' => 'string',
                'checkbox' => 'boolean',
                default => 'string',
            };

            if ($field->type === 'select' && $field->optionList() !== []) {
                $rule[] = 'in:'.implode(',', $field->optionList());
            }

            if (in_array($field->type, ['text', 'textarea'], true)) {
                $rule[] = 'max:2000';
            }

            $rules[$key] = implode('|', $rule);
        }

        return $rules;
    }

    /**
     * @return array<string, mixed>
     */
    public function extract(?int $departmentId, array $input): array
    {
        $fields = $this->forDepartment($departmentId);
        $given = $input['custom_fields'] ?? [];
        $values = [];

        foreach ($fields as $field) {
            if (array_key_exists($field->name, $given) && $given[$field->name] !== null && $given[$field->name] !== '') {
                $values[$field->name] = is_scalar($given[$field->name]) ? (string) $given[$field->name] : json_encode($given[$field->name]);
            }
        }

        return $values;
    }
}
