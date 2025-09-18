<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    use HasFactory;

    protected $fillable = [
        'numero_pedido',
        'cliente_id',
        'empleado_id',
        'fecha',
        'hora',
        'subtotal',
        'impuesto',
        'total',
        'estado',
        'observaciones'
    ];

    protected $casts = [
        'fecha' => 'date',
        'hora' => 'datetime:H:i:s',
    ];

    // Relación con Cliente (User)
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    // Relación con Empleado (User)
    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }

    // Relación con Detalles del Pedido
    public function detalles()
    {
        return $this->hasMany(DetallePedido::class);
    }

    // Generar número de pedido automáticamente
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($pedido) {
            $pedido->numero_pedido = 'PED-' . date('Y') . '-' . str_pad(static::count() + 1, 6, '0', STR_PAD_LEFT);
        });
    }
}
