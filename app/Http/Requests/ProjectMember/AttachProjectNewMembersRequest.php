<?php

namespace App\Http\Requests\ProjectMember;

use App\Enums\ProjectRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AttachProjectNewMembersRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id' => ['required', 'uuid'],
            'users' => ['required', 'array'],
            'users.*.id' => ['required', 'uuid'],
            'users.*.role' => ['required', Rule::enum(ProjectRole::class)]
        ];
    }

    public function attributes(): array
    {
        return [
            'id' => 'ID do projeto',
            'users' => 'usuários',
            'users.*.id' => 'ID do usuário',
            'users.*.role' => 'papel no projeto',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'id' => $this->route('id'),
        ]);
    }
}
