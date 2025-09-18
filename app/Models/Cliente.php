<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'apellido',
        'telefono',
        'email',
        'direccion',
        'fecha_nacimiento',
        'estado',
        'notas'
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
    ];

    // Accessor para obtener nombre completo
    public function getNombreCompletoAttribute()
    {
        return $this->nombre . ' ' . $this->apellido;
    }

    // Relación con pedidos
    public function pedidos()
    {
        return $this->hasMany(Pedido::class);
    }

    // Scope para clientes activos
    public function scopeActivo($query)
    {
        return $query->where('estado', 'activo');
    }
}
