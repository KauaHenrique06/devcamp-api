<?php

namespace App\Enums;

enum ProjectRole: string
{
    case MANAGER = 'manager';
    case LEAD = 'lead';
    case DEVELOPER = 'developer';

    public function label(): string
    {
        return match ($this) {
            self::MANAGER => 'Gestor do Projeto',
            self::LEAD => 'Lider',
            self::DEVELOPER => 'Desenvolvedor',
        };
    }

    public function canAttributeTasks(): bool
    {
        return in_array($this, [self::MANAGER, self::LEAD], true);
    }

    public function canAddMembers(): bool
    {
        return in_array($this, [self::MANAGER], true);
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
