<?php

namespace App\Http\Requests\Role;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleAddPermissionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => ['required', 'uuid'],
            'permissions' => ['required', 'array', 'min:1'],
            'permissions.*' => [
                'uuid',
                Rule::exists('permissions', 'id'),
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'id' => 'ID',
            'permissions' => 'permissões',
            'permissions.*' => 'permissão',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'id' => $this->route('id'),
        ]);
    }
}
