<?php

declare(strict_types=1);

namespace App\Enums;

enum PedidoStatus: string
{
    case Pending = 'pendiente';
    case Processing = 'procesando';
    case Completed = 'completado';
    case Cancelled = 'cancelado';
}
