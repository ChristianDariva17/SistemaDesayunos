<?php

return [
    'stock_minimo' => env('STOCK_MINIMO', 10),
    'cache_duracion' => env('REPORTES_CACHE', 300), // 5 minutos
    'pdf_orientacion' => 'portrait',
    'pdf_tamaño' => 'a4',
];
