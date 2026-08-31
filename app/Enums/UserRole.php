<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Technician = 'technician';

    /**
     * Human-readable label for the role.
     */
    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrador',
            self::Technician => 'Técnico',
        };
    }

    /**
     * Whether the role grants administrative privileges.
     */
    public function isAdmin(): bool
    {
        return $this === self::Admin;
    }
}
