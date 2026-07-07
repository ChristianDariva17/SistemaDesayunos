<?php

declare(strict_types=1);

use App\Support\Database\MigrationPreflight;

it('reports missing migration prerequisites with an actionable message', function (): void {
    MigrationPreflight::assertColumnsExist([
        'missing_business_table' => ['id'],
    ], 'example_integrity_migration.php');
})->throws(
    RuntimeException::class,
    'Cannot run example_integrity_migration.php because required database prerequisites are missing: missing_business_table. Run earlier migrations first, verify the target database schema, then rerun php artisan migrate.'
);
