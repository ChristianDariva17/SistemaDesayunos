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
        'impuesto',
        'total',
        'estado',
        'observaciones'
    ];

    protected $casts = [
        'fecha' => 'date',
        'hora' => 'datetime:H:i:s',
    ];

    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'pedido_producto')
                    ->withPivot('cantidad', 'precio_unitario', 'subtotal')
                    ->withTimestamps();
    }


    // Relación con Empleado (User)
    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
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
