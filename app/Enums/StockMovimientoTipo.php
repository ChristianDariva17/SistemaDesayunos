<?php

declare(strict_types=1);

namespace App\Enums;

enum StockMovimientoTipo: string
{
    case Entry = 'entrada';
    case Exit = 'salida';
    case Adjustment = 'ajuste';
    case Returned = 'devolucion';
    case Cancellation = 'cancelacion';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::Entry => 'Entrada',
            self::Exit => 'Salida',
            self::Adjustment => 'Ajuste',
            self::Returned => 'Devolución',
            self::Cancellation => 'Cancelación',
        };
    }
}
