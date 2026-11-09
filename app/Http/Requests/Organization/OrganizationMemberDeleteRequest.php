<?php

namespace App\Http\Requests\Organization;

use Illuminate\Foundation\Http\FormRequest;

class OrganizationMemberDeleteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => ['required', 'uuid'],
            'user_id' => ['required', 'uuid'],
        ];
    }

    public function attributes(): array
    {
        return [
            'id' => 'ID',
            'user_id' => 'usuário',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'id' => $this->route('id'),
            'user_id' => $this->route('userId'),
        ]);
    }
}
