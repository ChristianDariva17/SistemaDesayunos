<?php

declare(strict_types=1);

namespace App\Support\Database;

use Illuminate\Support\Facades\DB;
use PDO;
use RuntimeException;
use Throwable;

final class CheckConstraints
{
    /**
     * @var list<string>
     */
    private const SUPPORTED_DRIVERS = ['mysql', 'mariadb', 'pgsql', 'sqlsrv'];

    public static function supports(?string $driver = null): bool
    {
        return in_array($driver ?? DB::getDriverName(), self::SUPPORTED_DRIVERS, true);
    }

    public static function add(string $table, string $name, string $expression): void
    {
        DB::statement(self::addSql($table, $name, $expression));
    }

    public static function drop(string $table, string $name): void
    {
        DB::statement(self::dropSql($table, $name));
    }

    public static function addSql(string $table, string $name, string $expression, ?string $driver = null): string
    {
        self::assertSupported($driver);

        return sprintf('ALTER TABLE %s ADD CONSTRAINT %s CHECK (%s)', $table, $name, $expression);
    }

    public static function dropSql(string $table, string $name, ?string $driver = null, ?string $serverVersion = null): string
    {
        $driverProvided = $driver !== null;
        $driver ??= DB::getDriverName();
        $serverVersion ??= $driverProvided ? null : self::serverVersion();
        $dropDriver = self::dropDriver($driver, $serverVersion);

        return match ($dropDriver) {
            'mysql' => "ALTER TABLE {$table} DROP CHECK {$name}",
            'mariadb' => "ALTER TABLE {$table} DROP CONSTRAINT {$name}",
            'pgsql' => "ALTER TABLE {$table} DROP CONSTRAINT IF EXISTS {$name}",
            'sqlsrv' => "ALTER TABLE {$table} DROP CONSTRAINT {$name}",
            default => throw self::unsupportedDriver($driver),
        };
    }

    public static function assertSupported(?string $driver = null): void
    {
        $driver ??= DB::getDriverName();

        if (self::supports($driver)) {
            return;
        }

        throw self::unsupportedDriver($driver);
    }

    private static function unsupportedDriver(string $driver): RuntimeException
    {
        return new RuntimeException(sprintf(
            'Unsupported database driver "%s" for check constraints. Supported drivers: %s. '.
            'Use one of the supported production drivers or keep this migration path as a documented no-op for test-only drivers.',
            $driver,
            implode(', ', self::SUPPORTED_DRIVERS),
        ));
    }

    private static function dropDriver(string $driver, ?string $serverVersion): string
    {
        if ($driver === 'mysql' && $serverVersion !== null && str_contains(strtolower($serverVersion), 'mariadb')) {
            return 'mariadb';
        }

        return $driver;
    }

    private static function serverVersion(): ?string
    {
        try {
            $version = DB::connection()->getPdo()->getAttribute(PDO::ATTR_SERVER_VERSION);
        } catch (Throwable) {
            return null;
        }

        return is_string($version) ? $version : null;
    }
}
