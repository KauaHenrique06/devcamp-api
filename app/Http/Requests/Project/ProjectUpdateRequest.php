<?php

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;

class ProjectUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => ['required', 'uuid'],
            'name' => ['required_without_all:description,base_color_hex,logo', 'string', 'max:255'],
            'description' => ['required_without_all:name,base_color_hex,logo', 'string'],
            'base_color_hex' => ['required_without_all:name,description,logo', 'nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'logo' => ['required_without_all:name,description,base_color_hex', 'nullable', 'file','mimetypes:image/png,image/jpeg,image/webp,image/svg+xml', 'max:2048'],
        ];
    }

    public function attributes(): array
    {
        return [
            'id' => 'ID',
            'name' => 'nome',
            'description' => 'descrição',
            'base_color_hex' => 'cor base',
            'logo' => 'logo',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'id' => $this->route('id'),
        ]);
    }
}
