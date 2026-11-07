<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:6'],
            'role_id' => [
                'required',
                'uuid',
                Rule::exists('roles', 'id')->where('guard_name', 'api'),
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nome',
            'email' => 'e-mail',
            'password' => 'senha',
            'role_id' => 'cargo',
        ];
    }
}
