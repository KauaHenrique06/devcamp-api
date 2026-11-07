<?php

namespace App\Enums;

enum SocialLoginProviders: string
{
    case MICROSOFT = 'microsoft';
    case GOOGLE    = 'google';
    case GITHUB = 'github';

    public function label()
    {
        return match($this) {
            self::GOOGLE    => 'Google',
            self::MICROSOFT => 'Microsoft',
            self::GITHUB => 'GitHub'
        };
    }
}
