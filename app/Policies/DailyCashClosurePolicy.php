<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\DailyCashClosure;
use App\Models\User;
use App\Support\RoleNormalizer;

final class DailyCashClosurePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function view(User $user, DailyCashClosure $dailyCashClosure): bool
    {
        return $this->isAdmin($user);
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user);
    }

    private function isAdmin(User $user): bool
    {
        return RoleNormalizer::isAdministrator((string) $user->rol);
    }
}
