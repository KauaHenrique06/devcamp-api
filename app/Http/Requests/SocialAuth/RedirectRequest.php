<?php

namespace App\Http\Requests\SocialAuth;

use App\Enums\SocialLoginProviders;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RedirectRequest extends FormRequest
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
            'provider' => [
                'required', 
                'string', 
                Rule::enum(SocialLoginProviders::class)
            ],
            'redirect_uri' => [
                'sometimes',
                'string',
                'max:2048'
            ]
        ];
    }

    public function attributes(): array
    {
        return [
            'provider'     => 'Social login provider',
            'redirect_uri' => 'Redirect URI' 
        ];
    }

    protected function prepareForValidation(): void 
    {
        $this->merge([
            'provider' => $this->route('provider')
        ]);
    }
}
