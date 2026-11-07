<?php

namespace App\Http\Requests\User;

use App\Helpers\Requests\Utils\PageRuleHelper;
use App\Helpers\Requests\Utils\PerPageRuleHelper;
use App\Helpers\Requests\Utils\SearchRuleHelper;
use Illuminate\Foundation\Http\FormRequest;

class UserIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            ...PerPageRuleHelper::rule(),
            ...PageRuleHelper::rule(),
            ...SearchRuleHelper::rule(),
        ];
    }

    public function attributes(): array
    {
        return [
            ...PerPageRuleHelper::attribute(),
            ...PageRuleHelper::attribute(),
            ...SearchRuleHelper::attribute(),
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            ...PerPageRuleHelper::prepareForValidation($this),
            ...PageRuleHelper::prepareForValidation($this),
            ...SearchRuleHelper::prepareForValidation($this),
        ]);
    }
}
