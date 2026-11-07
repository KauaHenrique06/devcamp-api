<?php

namespace App\Http\Requests\Role;

use Illuminate\Foundation\Http\FormRequest;

class RoleShowRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => ['required', 'uuid'],
        ];
    }

    public function attributes(): array
    {
        return [
            'id' => 'ID',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['id' => $this->route('id')]);
    }
}
