<?php

namespace App\Helpers\Requests\Utils;

class ByRuleHelper
{
    /**
     * Get the validation rules for the admin ID.
     *
     * @return array The validation rules.
     */
    public static function rule(): array
    {
        return [
            'by' => [
                'sometimes',
                'string',
                "in:desc,asc",
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
            'by' => 'Por'
        ];
    }

    public static function prepareForValidation($query): array
    {
        return [
            'by' => $query->query('by', '')
        ];
    }
}