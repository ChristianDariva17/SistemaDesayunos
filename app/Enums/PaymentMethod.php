<?php

declare(strict_types=1);

namespace App\Enums;

enum PaymentMethod: string
{
    case Cash = 'efectivo';
    case Card = 'tarjeta';
    case Transfer = 'transferencia';
    case Other = 'otro';
}
