<?php

namespace App\Helpers\Requests\Utils;

class OrderRuleHelper
{
    /**
     * Get the validation rules for the admin ID.
     *
     * @return array The validation rules.
     */
    public static function rule(string $in): array
    {
        return [
            'order' => [
                'sometimes',
                'string',
                "in:{$in}",
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
            'order' => 'Ordenação'
        ];
    }

    public static function prepareForValidation($query): array
    {
        return [
            'order' => $query->query('order', '')
        ];
    } 
}