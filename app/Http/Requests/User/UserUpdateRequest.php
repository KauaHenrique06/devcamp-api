<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'id' => ['required', 'uuid'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($id)],
            'password' => ['nullable', 'string', 'min:6'],
            'roles' => ['nullable', 'array'],
            'roles.*' => [
                'string',
                Rule::exists('roles', 'name')->where('guard_name', 'api'),
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'id' => 'ID',
            'name' => 'nome',
            'email' => 'e-mail',
            'password' => 'senha',
            'roles' => 'cargos',
            'roles.*' => 'cargo',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'id' => $this->route('id'),
        ]);
    }
}
