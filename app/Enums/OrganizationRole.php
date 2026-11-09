<?php

namespace App\Enums;

enum OrganizationRole: string
{
    case OWNER = 'owner';
    case ADMIN = 'admin';
    case MEMBER = 'member';

    public function label(): string
    {
        return match ($this) {
            self::OWNER => 'Dono',
            self::ADMIN => 'Administrador',
            self::MEMBER => 'Membro',
        };
    }

    /** Owner e admin podem editar a organização e gerenciar membros. */
    public function canManage(): bool
    {
        return in_array($this, [self::OWNER, self::ADMIN], true);
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
