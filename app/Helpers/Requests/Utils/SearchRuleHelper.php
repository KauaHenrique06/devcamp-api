<?php

namespace App\Helpers\Requests\Utils;

class SearchRuleHelper
{
    /**
     * Get the validation rules for the admin ID.
     *
     * @return array The validation rules.
     */
    public static function rule(): array
    {
        return [
            'search' => [
                'sometimes',
                'string',
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
            'search' => 'Pesquisa'
        ];
    }

    public static function prepareForValidation($query, $defaultParam = ''): array
    {
        return [
            'search' => $query->query('search', $defaultParam)
        ];
    }
}