<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class GenerarReporteInventario implements ShouldQueue
{
    use Queueable;

    public function handle()
    {
        // Generar reporte en segundo plano
        // Enviar email cuando termine
    }
}
