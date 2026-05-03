<?php

namespace App\Enum;

enum UserRole: string
{
    case USER = 'ROLE_USER';
    case ADMIN = 'ROLE_ADMIN';

    public function label(): string
    {
        return match($this) {
            self::USER => 'Benutzer',
            self::ADMIN => 'Administrator',
        };
    }
}
