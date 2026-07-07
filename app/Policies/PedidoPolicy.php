<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Pedido;
use App\Models\User;
use App\Support\RoleNormalizer;

final class PedidoPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->isStaff($user);
    }

    public function view(User $user, Pedido $pedido): bool
    {
        return $this->isStaff($user);
    }

    public function create(User $user): bool
    {
        return $this->isStaff($user);
    }

    public function update(User $user, Pedido $pedido): bool
    {
        return $this->isAdmin($user);
    }

    public function delete(User $user, Pedido $pedido): bool
    {
        return $this->isStaff($user);
    }

    public function changeStatus(User $user, Pedido $pedido): bool
    {
        return $this->update($user, $pedido);
    }

    public function duplicate(User $user, Pedido $pedido): bool
    {
        return $this->isStaff($user);
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
