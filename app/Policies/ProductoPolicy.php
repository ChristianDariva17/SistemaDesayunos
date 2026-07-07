<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Producto;
use App\Models\User;
use App\Support\RoleNormalizer;

final class ProductoPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->isStaff($user);
    }

    public function view(User $user, Producto $producto): bool
    {
        return $this->isStaff($user);
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function update(User $user, Producto $producto): bool
    {
        return $this->isAdmin($user);
    }

    public function delete(User $user, Producto $producto): bool
    {
        return $this->isAdmin($user);
    }

    public function toggleStatus(User $user, Producto $producto): bool
    {
        return $this->update($user, $producto);
    }

    public function updateStock(User $user, Producto $producto): bool
    {
        return $this->update($user, $producto);
    }

    public function duplicate(User $user, Producto $producto): bool
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
