<?php

namespace App\Http\Requests\Role;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $guardName = $this->input('guard_name', 'api');

        return [
            'id' => ['required', 'uuid'],
            'name' => ['required', 'string', 'max:255'],
            'guard_name' => ['nullable', 'string', 'max:255'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => [
                'string',
                Rule::exists('permissions', 'name')->where('guard_name', $guardName),
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'id' => 'ID',
            'name' => 'nome',
            'guard_name' => 'guard',
            'permissions' => 'permissões',
            'permissions.*' => 'permissão',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'id' => $this->route('id'),
        ]);

        if (! $this->has('guard_name')) {
            $this->merge(['guard_name' => 'api']);
        }
    }
}
