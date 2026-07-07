<?php

declare(strict_types=1);

namespace App\Support;

final class RoleNormalizer
{
    public const ADMINISTRATOR = 'administrador';

    public const WORKER = 'trabajador';

    public static function normalize(string $role): string
    {
        return match ($role) {
            'admin' => self::ADMINISTRATOR,
            'empleado' => self::WORKER,
            default => $role,
        };
    }

    /**
     * @param  array<int, string>  $roles
     * @return array<int, string>
     */
    public static function normalizeMany(array $roles): array
    {
        return array_map(static fn (string $role): string => self::normalize($role), $roles);
    }

    public static function isAdministrator(?string $role): bool
    {
        return $role !== null && self::normalize($role) === self::ADMINISTRATOR;
    }

    public static function isStaff(?string $role): bool
    {
        return in_array(self::normalize((string) $role), [self::ADMINISTRATOR, self::WORKER], true);
    }
}
