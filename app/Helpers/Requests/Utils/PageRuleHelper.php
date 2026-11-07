<?php

namespace App\Helpers\Requests\Utils;

class PageRuleHelper
{
    /**
     * Get the validation rules for the admin ID.
     *
     * @return array The validation rules.
     */
    public static function rule(): array
    {
        return [
            'page' => [
                'sometimes',
                'integer',
            ],
        ];
    }

    /**
     * Get the attributes for the validation rule.
     *
     * @return array
     */
    public static function attribute(): array
    {
        return [
            'page' => 'Página'
        ];
    }

    public static function prepareForValidation($query): array
    {
        return [
            'page' => $query->query('page', 1)
        ];
    }
}