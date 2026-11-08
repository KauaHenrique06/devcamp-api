<?php

namespace App\Http\Requests\Organization;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class OrganizationUpdateRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255', Rule::unique('organizations', 'name')->ignore($id)],
            'slug' => ['sometimes', 'string', 'max:255', 'alpha_dash', Rule::unique('organizations', 'slug')->ignore($id)],
            'description' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
        ];
    }

    public function attributes(): array
    {
        return [
            'id' => 'ID',
            'name' => 'nome',
            'slug' => 'slug',
            'description' => 'descrição',
            'logo' => 'logo',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'id' => $this->route('id'),
        ]);

        // Só normaliza o slug se ele for enviado; omitir mantém o atual.
        if ($this->filled('slug')) {
            $this->merge(['slug' => Str::slug($this->input('slug'))]);
        }
    }
}
