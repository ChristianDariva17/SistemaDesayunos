<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Cliente;
use App\Models\User;
use App\Support\RoleNormalizer;

final class ClientePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->isStaff($user);
    }

    public function view(User $user, Cliente $cliente): bool
    {
        return $this->isStaff($user);
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function update(User $user, Cliente $cliente): bool
    {
        return $this->isAdmin($user);
    }

    public function delete(User $user, Cliente $cliente): bool
    {
        return $this->isAdmin($user);
    }

    public function toggleStatus(User $user, Cliente $cliente): bool
    {
        return $this->update($user, $cliente);
    }

    public function duplicate(User $user, Cliente $cliente): bool
    {
        return $this->create($user);
    }

    public function export(User $user): bool
    {
        return $this->isAdmin($user);
    }

    private function isStaff(User $user): bool
    {
        return RoleNormalizer::isStaff((string) $user->rol);
    }

    private function isAdmin(User $user): bool
    {
        return RoleNormalizer::isAdministrator((string) $user->rol);
    }
}
