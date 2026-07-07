<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Support\RoleNormalizer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'rol',
    ];

    public function stockMovimientos(): HasMany
    {
        return $this->hasMany(StockMovimiento::class);
    }

    public function audits(): HasMany
    {
        return $this->hasMany(Audit::class);
    }

    public function dailyCashClosures(): HasMany
    {
        return $this->hasMany(DailyCashClosure::class, 'closed_by_user_id');
    }

    // Agregar estos métodos al final de la clase
    public function esAdministrador(): bool
    {
        return RoleNormalizer::isAdministrator((string) $this->rol);
    }

    public function esTrabajador(): bool
    {
        return RoleNormalizer::normalize((string) $this->rol) === RoleNormalizer::WORKER;
    }

    public function getRolNombreAttribute(): string
    {
        return match (RoleNormalizer::normalize((string) $this->rol)) {
            RoleNormalizer::ADMINISTRATOR => 'Administrador',
            RoleNormalizer::WORKER => 'Trabajador',
            default => 'Usuario'
        };
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
