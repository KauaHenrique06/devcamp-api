<?php

namespace App\Helpers\Requests\Utils;

class PerPageRuleHelper
{
    /**
     * Get the validation rules for the admin ID.
     *
     * @return array The validation rules.
     */
    public static function rule(): array
    {
        return [
            'per_page' => [
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
            'per_page' => 'Por página'
        ];
    }

    public static function prepareForValidation($query): array
    {
        return [
            'per_page' => $query->query('per_page', 10)
        ];
    }
}