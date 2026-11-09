<?php

namespace App\Http\Requests\Organization;

use App\Enums\OrganizationRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrganizationMemberStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => ['required', 'uuid'],
            'user_id' => ['required', 'uuid', Rule::exists('users', 'id')->whereNull('deleted_at')],
            'role' => ['required', Rule::enum(OrganizationRole::class)],
        ];
    }

    public function attributes(): array
    {
        return [
            'id' => 'ID',
            'user_id' => 'usuário',
            'role' => 'papel',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'id' => $this->route('id'),
            'role' => $this->input('role', OrganizationRole::MEMBER->value),
        ]);
    }
}
