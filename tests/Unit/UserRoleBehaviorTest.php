<?php

declare(strict_types=1);

use App\Models\User;

function userWithRole(?string $role): User
{
    return new User([
        'rol' => $role,
    ]);
}

test('administrator helper accepts canonical and legacy administrator roles', function (string $role): void {
    $user = userWithRole($role);

    expect($user->esAdministrador())->toBeTrue()
        ->and($user->esTrabajador())->toBeFalse();
})->with([
    'canonical administrator role' => 'administrador',
    'legacy administrator alias' => 'admin',
]);

test('worker helper accepts canonical and legacy worker roles', function (string $role): void {
    $user = userWithRole($role);

    expect($user->esTrabajador())->toBeTrue()
        ->and($user->esAdministrador())->toBeFalse();
})->with([
    'canonical worker role' => 'trabajador',
    'legacy worker alias' => 'empleado',
]);

test('role name accessor displays normalized canonical and legacy role names', function (string $role, string $expectedName): void {
    expect(userWithRole($role)->rol_nombre)->toBe($expectedName);
})->with([
    'canonical administrator role' => ['administrador', 'Administrador'],
    'legacy administrator alias' => ['admin', 'Administrador'],
    'canonical worker role' => ['trabajador', 'Trabajador'],
    'legacy worker alias' => ['empleado', 'Trabajador'],
]);

test('role helpers reject unsupported roles and display the fallback role name', function (?string $role): void {
    $user = userWithRole($role);

    expect($user->esAdministrador())->toBeFalse()
        ->and($user->esTrabajador())->toBeFalse()
        ->and($user->rol_nombre)->toBe('Usuario');
})->with([
    'unsupported role' => 'supervisor',
    'missing role' => null,
]);
