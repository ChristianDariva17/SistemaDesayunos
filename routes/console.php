<?php

use App\Models\Producto;
use App\Services\ProductImageService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('products:generate-thumbnails {--chunk=100}', function (ProductImageService $images): int {
    $chunkSize = filter_var($this->option('chunk'), FILTER_VALIDATE_INT);

    if (! is_int($chunkSize) || $chunkSize < 1 || $chunkSize > 500) {
        $this->error('The chunk option must be an integer between 1 and 500.');

        return 1;
    }

    $generated = 0;
    $skipped = 0;
    $failed = 0;

    Producto::query()
        ->whereNotNull('imagen')
        ->select(['id', 'imagen'])
        ->chunkById($chunkSize, function ($productos) use ($images, &$generated, &$skipped, &$failed): void {
            foreach ($productos as $producto) {
                try {
                    $images->generateThumbnail((string) $producto->imagen) ? $generated++ : $skipped++;
                } catch (Throwable $exception) {
                    $failed++;
                    $this->error("Product {$producto->id}: {$exception->getMessage()}");
                }
            }
        });

    $this->info("Generated: {$generated}; skipped: {$skipped}; failed: {$failed}");

    return $failed === 0 ? 0 : 1;
})->purpose('Generate list thumbnails for existing product images');
