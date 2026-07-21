<?php

return [
    'stock_minimo' => env('STOCK_MINIMO', 10),
    'cache_duracion' => env('REPORTES_CACHE', 300), // 5 minutos
    'csv_chunk_size' => max(1, (int) env('REPORTES_CSV_CHUNK_SIZE', 500)),
    'pdf_sync_max_rows' => max(1, (int) env('REPORTES_PDF_SYNC_MAX_ROWS', 250)),
    'pdf_sync_max_days' => max(1, (int) env('REPORTES_PDF_SYNC_MAX_DAYS', 31)),
    'pdf_orientacion' => 'portrait',
    'pdf_tamaño' => 'a4',
];
