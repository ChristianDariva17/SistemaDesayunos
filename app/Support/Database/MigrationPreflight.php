<?php

declare(strict_types=1);

namespace App\Support\Database;

use Illuminate\Support\Facades\Schema;
use RuntimeException;

final class MigrationPreflight
{
    /**
     * @param  array<string, list<string>>  $requirements
     */
    public static function assertColumnsExist(array $requirements, string $migrationName): void
    {
        $missing = [];

        foreach ($requirements as $table => $columns) {
            if (! Schema::hasTable($table)) {
                $missing[] = $table;

                continue;
            }

            foreach ($columns as $column) {
                if (! Schema::hasColumn($table, $column)) {
                    $missing[] = "{$table}.{$column}";
                }
            }
        }

        if ($missing === []) {
            return;
        }

        throw new RuntimeException(sprintf(
            'Cannot run %s because required database prerequisites are missing: %s. '.
            'Run earlier migrations first, verify the target database schema, then rerun php artisan migrate.',
            $migrationName,
            implode(', ', $missing),
        ));
    }
}
