<?php

namespace App\Enums;

enum SocialLoginProviders: string
{
    case MICROSOFT = 'microsoft';
    case GOOGLE    = 'google';

    public function label()
    {
        return match($this) {
            self::GOOGLE    => 'Google',
            self::MICROSOFT => 'Microsoft'
        };
    }
}
