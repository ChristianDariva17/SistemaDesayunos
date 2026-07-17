<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\StockMovimientoTipo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovimiento extends Model
{
    use HasFactory;

    public const TIPO_ENTRADA = StockMovimientoTipo::Entry->value;

    public const TIPO_SALIDA = StockMovimientoTipo::Exit->value;

    public const TIPO_AJUSTE = StockMovimientoTipo::Adjustment->value;

    public const TIPO_DEVOLUCION = StockMovimientoTipo::Returned->value;

    public const TIPO_CANCELACION = StockMovimientoTipo::Cancellation->value;

    public const TIPOS = [
        self::TIPO_ENTRADA,
        self::TIPO_SALIDA,
        self::TIPO_AJUSTE,
        self::TIPO_DEVOLUCION,
        self::TIPO_CANCELACION,
    ];

    protected $table = 'stock_movimientos';

    protected $fillable = [
        'producto_id',
        'pedido_id',
        'pedido_numero',
        'user_id',
        'tipo',
        'cantidad',
        'stock_anterior',
        'stock_nuevo',
        'motivo',
    ];

    protected $casts = [
        'producto_id' => 'integer',
        'pedido_id' => 'integer',
        'pedido_numero' => 'string',
        'user_id' => 'integer',
        'cantidad' => 'integer',
        'stock_anterior' => 'integer',
        'stock_nuevo' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function tipoEnum(): ?StockMovimientoTipo
    {
        return StockMovimientoTipo::tryFrom((string) $this->tipo);
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
