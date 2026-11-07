<?php

namespace App\Http\Requests\Role;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $guardName = $this->input('guard_name', 'api');

        return [
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
            'name' => 'nome',
            'guard_name' => 'guard',
            'permissions' => 'permissões',
            'permissions.*' => 'permissão',
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->has('guard_name')) {
            $this->merge(['guard_name' => 'api']);
        }
    }
}
