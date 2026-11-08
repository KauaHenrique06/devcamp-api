<?php

namespace App\Http\Requests\Organization;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class OrganizationStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('organizations', 'name')],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('organizations', 'slug')],
            'description' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nome',
            'slug' => 'slug',
            'description' => 'descrição',
            'logo' => 'logo',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Gera o slug a partir do nome quando não for enviado.
        $this->merge([
            'slug' => Str::slug($this->input('slug') ?: (string) $this->input('name')),
        ]);
    }
}
